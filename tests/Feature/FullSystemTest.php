<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GeneratedDocument;
use App\Models\Payment;
use App\Models\PromoCode;
use App\Models\Template;
use App\Models\User;
use App\Services\AfricasTalkingService;
use App\Services\PayHeroService;
use Database\Seeders\CategorySeeder;
use Database\Seeders\TemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class FullSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
        $this->seed(TemplateSeeder::class);
    }

    public function test_home_page_loads_with_categories(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Letters');
        $response->assertSee('Affidavits');
    }

    public function test_category_page_displays_templates(): void
    {
        $response = $this->get('/category/letters');
        $response->assertStatus(200);
        $response->assertSee('Application Letter');
    }

    public function test_builder_page_loads_for_template(): void
    {
        $response = $this->get('/builder/application-letter');
        $response->assertStatus(200);
        $response->assertSee('Application Letter');
    }

    public function test_sitemap_returns_xml(): void
    {
        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('<urlset', $response->getContent());
    }

    public function test_robots_returns_txt(): void
    {
        $response = $this->get('/robots.txt');
        $response->assertStatus(200);
        $this->assertStringContainsString('User-agent:', $response->getContent());
    }

    public function test_auth_otp_flow(): void
    {
        $capturedOtp = null;

        // Mock AfricasTalkingService to capture generated OTP
        $mockAt = Mockery::mock(AfricasTalkingService::class);
        $mockAt->shouldReceive('sendSms')
            ->once()
            ->andReturnUsing(function ($phone, $message) use (&$capturedOtp) {
                if (preg_match('/code is: (\d{6})/', $message, $matches)) {
                    $capturedOtp = $matches[1];
                }
                return true;
            });
        $this->app->instance(AfricasTalkingService::class, $mockAt);

        // 1. Request OTP
        $res = $this->postJson('/api/auth/send-otp', [
            'phone' => '0712345678',
        ]);
        $res->assertStatus(200);
        $res->assertJson(['success' => true]);
        $this->assertNotNull($capturedOtp, 'OTP was captured from SMS service');

        // 2. Verify with wrong OTP fails
        $failRes = $this->postJson('/api/auth/verify-otp', [
            'phone' => '0712345678',
            'otp'   => '000000',
        ]);
        $failRes->assertStatus(422);

        // 3. Verify with correct OTP succeeds
        $okRes = $this->postJson('/api/auth/verify-otp', [
            'phone' => '0712345678',
            'otp'   => $capturedOtp,
        ]);
        $okRes->assertStatus(200);
        $okRes->assertJson(['success' => true]);
        $this->assertAuthenticated();

        // 4. Logout
        $logoutRes = $this->postJson('/api/auth/logout');
        $logoutRes->assertStatus(200);
        $this->assertGuest();
    }

    public function test_document_preview_generation(): void
    {
        $template = Template::where('slug', 'application-letter')->firstOrFail();

        $formData = [
            'applicant_name'    => 'John Doe',
            'applicant_address' => 'P.O. Box 100, Nairobi',
            'applicant_phone'   => '0712345678',
            'applicant_email'   => 'john@example.com',
            'date'              => '2026-10-10',
            'recipient_name'    => 'HR Manager',
            'company_name'      => 'Acme Corp',
            'company_address'   => 'P.O. Box 200, Nairobi',
            'position_applied'  => 'Software Engineer',
            'qualifications'    => 'Degree in Computer Science',
        ];

        $res = $this->postJson('/api/documents/application-letter/preview', $formData);
        $res->assertStatus(200);
        $res->assertJson(['success' => true]);
        $token = $res->json('session_token');
        $this->assertNotEmpty($token);

        // Preview file route
        $previewRes = $this->get('/preview-file/' . $token);
        $previewRes->assertStatus(200);
        $this->assertEquals('application/pdf', $previewRes->headers->get('Content-Type'));
    }

    public function test_promo_code_validation_endpoint(): void
    {
        PromoCode::create([
            'code'      => 'DISCOUNT20',
            'type'      => 'percent',
            'value'     => 20,
            'is_active' => true,
        ]);

        $res = $this->postJson('/api/promo/validate', [
            'code'   => 'DISCOUNT20',
            'amount' => 50,
        ]);

        $res->assertStatus(200);
        $res->assertJson([
            'success'  => true,
            'discount' => 10.0,
        ]);
    }

    public function test_payment_initiation_and_webhook(): void
    {
        $template = Template::where('slug', 'application-letter')->firstOrFail();
        $token = Str::random(32);

        $doc = GeneratedDocument::create([
            'session_token' => $token,
            'template_id'   => $template->id,
            'form_data'     => ['applicant_name' => 'Jane Doe'],
            'status'        => 'draft',
        ]);

        $webhookSecret = 'test_secret_12345';
        config(['services.payhero.webhook_secret' => $webhookSecret]);

        // Mock PayHeroService initiate
        $mockPayHero = Mockery::mock(PayHeroService::class)->makePartial();
        $mockPayHero->shouldReceive('initiateStk')
            ->once()
            ->andReturn([
                'success'   => true,
                'reference' => 'PH-REF-12345',
                'message'   => 'STK Push sent',
                'data'      => [],
            ]);
        $mockPayHero->shouldReceive('hasWebhookSecret')->andReturn(true);
        $mockPayHero->shouldReceive('verifySignature')->andReturn(true);
        $this->app->instance(PayHeroService::class, $mockPayHero);

        $payRes = $this->postJson('/api/payments/initiate', [
            'session_token' => $token,
            'phone'         => '0712345678',
        ]);
        $payRes->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'generated_document_id' => $doc->id,
            'phone'                 => '0712345678',
        ]);

        $payment = Payment::where('generated_document_id', $doc->id)->firstOrFail();

        $webhookPayload = [
            'external_reference'   => 'KD-' . $payment->id . '-' . substr($token, 0, 16),
            'status'               => 'success',
            'mpesa_receipt_number' => 'QWE123RTY',
            'amount'               => 50.0,
        ];
        $rawJson = json_encode($webhookPayload);
        $signature = hash_hmac('sha256', $rawJson, $webhookSecret);

        $webhookRes = $this->call('POST', '/api/webhooks/payhero', [], [], [], [
            'CONTENT_TYPE'         => 'application/json',
            'HTTP_X_PAYHERO_SIGNATURE' => $signature,
        ], $rawJson);

        $webhookRes->assertStatus(200);

        // Check payment and doc updated to paid
        $payment->refresh();
        $doc->refresh();
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals('paid', $doc->status);
        $this->assertEquals('QWE123RTY', $payment->mpesa_receipt);
    }

    public function test_cv_assistant_generate_api(): void
    {
        $cvData = [
            'full_name'  => 'David Omondi',
            'job_title'  => 'Full Stack Engineer',
            'phone'      => '0712345678',
            'email'      => 'david@omondi.co.ke',
            'location'   => 'Nairobi, Kenya',
            'summary'    => 'Experienced software developer with 5+ years building scalable web systems in PHP and JavaScript.',
            'experience' => 'Senior Developer at Tech Kenya (2021-Present): Led backend architecture.',
            'education'  => 'BSc Computer Science, University of Nairobi, 2020',
            'skills'     => 'PHP, Laravel, MySQL, JavaScript, Docker',
        ];

        $res = $this->postJson('/api/cv/generate', $cvData);
        $res->assertStatus(200);
        $res->assertJson(['success' => true]);
        $this->assertStringContainsString('DAVID OMONDI', $res->json('cv_text'));
        $this->assertStringContainsString('cv-preview-sheet', $res->json('cv_html'));
    }

    public function test_cv_assistant_analyze_api(): void
    {
        $sampleText = "DAVID OMONDI\nPhone: 0712345678\nEmail: david@example.com\nSummary: Software engineer with experience.\nEducation: University of Nairobi BSc CS\nWork Experience: Spearheaded 5 projects and managed systems for 50+ clients, increasing revenue by 25%.\nSkills: Laravel, PHP, MySQL\nReferees: Available upon request.";

        $res = $this->postJson('/api/cv/analyze', [
            'cv_text' => $sampleText,
        ]);

        $res->assertStatus(200);
        $res->assertJson(['success' => true]);
        $this->assertArrayHasKey('score', $res->json('analysis'));
        $this->assertArrayHasKey('modified_cv', $res->json());
    }

    public function test_admin_routes_protected(): void
    {
        // Unauthenticated access
        $res = $this->getJson('/api/admin/stats');
        $res->assertStatus(401);

        // Normal user access (non-admin)
        $user = User::create([
            'phone'    => '0711111111',
            'name'     => 'Regular User',
            'is_admin' => false,
        ]);

        $resUser = $this->actingAs($user)->getJson('/api/admin/stats');
        $resUser->assertStatus(403);

        // Admin user access
        $admin = User::create([
            'phone'    => '0722222222',
            'name'     => 'Admin User',
            'is_admin' => true,
        ]);

        $resAdmin = $this->actingAs($admin)->getJson('/api/admin/stats');
        $resAdmin->assertStatus(200);
        $resAdmin->assertJson(['success' => true]);
    }
}
