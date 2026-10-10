<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GeneratedDocument;
use App\Models\LibraryItem;
use App\Models\Payment;
use App\Models\PromoCode;
use App\Models\Template;
use App\Models\User;
use App\Services\StorageService;
use App\Services\TelegramService;
use Database\Seeders\CategorySeeder;
use Database\Seeders\TemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class AdvancedSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
        $this->seed(TemplateSeeder::class);
    }

    public function test_document_download_flow_pdf_and_word(): void
    {
        $template = Template::where('slug', 'application-letter')->firstOrFail();
        $token = Str::random(32);

        $doc = GeneratedDocument::create([
            'session_token' => $token,
            'template_id'   => $template->id,
            'form_data'     => [
                'applicant_name'    => 'Brian Mwangi',
                'applicant_address' => 'P.O. Box 55, Nakuru',
                'applicant_phone'   => '0712345678',
                'date'              => '2026-10-10',
                'recipient_name'    => 'Managing Director',
                'company_name'      => 'Safaricom PLC',
                'company_address'   => 'Waiyaki Way, Nairobi',
                'position_applied'  => 'DevOps Engineer',
                'qualifications'    => 'AWS certified developer',
            ],
            'status'        => 'paid',
        ]);

        // Access download page with doc token in session
        session(['doc_token_' . $token => true]);

        $pageRes = $this->get('/download/' . $token);
        $pageRes->assertStatus(200);
        $pageRes->assertSee('Download');

        // Download PDF
        $pdfRes = $this->get('/download/' . $token . '/pdf');
        $pdfRes->assertStatus(200);

        // Download Word
        $wordRes = $this->get('/download/' . $token . '/word');
        $wordRes->assertStatus(200);
    }

    public function test_document_re_generation_edit_window_and_limit(): void
    {
        $template = Template::where('slug', 'application-letter')->firstOrFail();
        $token = Str::random(32);

        $doc = GeneratedDocument::create([
            'session_token'          => $token,
            'template_id'            => $template->id,
            'form_data'              => ['applicant_name' => 'Brian'],
            'status'                 => 'paid',
            'edit_count'             => 3,
            'edit_window_expires_at' => now()->addHours(12),
        ]);

        $res = $this->postJson('/api/documents/' . $token . '/generate');
        $res->assertStatus(422);
        $res->assertJson(['message' => 'Maximum edits (3) reached.']);
    }

    public function test_library_search_and_missing_document_request(): void
    {
        // 1. Search for non-existent document
        $res = $this->get('/library/search?q=NonExistentPassportFormXYZ');
        $res->assertStatus(200);

        // Verify missing document request was recorded
        $this->assertDatabaseHas('missing_document_requests', [
            'search_query' => 'NonExistentPassportFormXYZ',
        ]);
    }

    public function test_library_download_stream(): void
    {
        $item = LibraryItem::create([
            'title'            => 'Sample Form',
            'description'      => 'Sample Kenyan government form',
            'file_name'        => 'sample_form.pdf',
            'telegram_file_id' => 'tg_file_12345',
            'file_size'        => 1024,
            'mime_type'        => 'application/pdf',
            'category'         => 'Forms',
            'is_free'          => true,
            'is_active'        => true,
        ]);

        // Mock TelegramService
        $mockTg = Mockery::mock(TelegramService::class);
        $mockTg->shouldReceive('streamFile')
            ->once()
            ->with('tg_file_12345')
            ->andReturn(new StreamedResponse(function () {
                echo 'fake-pdf-content';
            }, 200, ['Content-Type' => 'application/pdf']));
        $this->app->instance(TelegramService::class, $mockTg);

        $res = $this->get(route('library.download', $item->id));
        $res->assertStatus(200);

        $item->refresh();
        $this->assertEquals(1, $item->download_count);
    }

    public function test_admin_payment_unlock_updates_payment_status(): void
    {
        $admin = User::create([
            'phone'    => '0700000000',
            'name'     => 'Super Admin',
            'is_admin' => true,
        ]);

        $template = Template::where('slug', 'application-letter')->firstOrFail();
        $token = Str::random(32);

        $doc = GeneratedDocument::create([
            'session_token' => $token,
            'template_id'   => $template->id,
            'form_data'     => ['applicant_name' => 'Alice'],
            'status'        => 'draft',
        ]);

        $payment = Payment::create([
            'generated_document_id' => $doc->id,
            'phone'                 => '0712345678',
            'amount'                => 50.00,
            'status'                => 'pending',
        ]);

        $res = $this->actingAs($admin)->postJson('/api/admin/payments/' . $payment->id . '/unlock');
        $res->assertStatus(200);

        $doc->refresh();
        $this->assertEquals('paid', $doc->status);
    }

    public function test_user_deletion_with_payment(): void
    {
        $user = User::create([
            'phone'    => '0712345678',
            'name'     => 'John Doe',
            'is_admin' => false,
        ]);

        $template = Template::where('slug', 'application-letter')->firstOrFail();

        $doc = GeneratedDocument::create([
            'user_id'       => $user->id,
            'session_token' => Str::random(32),
            'template_id'   => $template->id,
            'form_data'     => ['applicant_name' => 'John'],
            'status'        => 'paid',
        ]);

        Payment::create([
            'generated_document_id' => $doc->id,
            'user_id'               => $user->id,
            'phone'                 => '0712345678',
            'amount'                => 50.00,
            'status'                => 'paid',
        ]);

        $res = $this->actingAs($user)->deleteJson('/account');
        $res->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
