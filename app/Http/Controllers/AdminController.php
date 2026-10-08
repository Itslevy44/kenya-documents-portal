<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\GeneratedDocument;
use App\Models\LibraryItem;
use App\Models\Payment;
use App\Models\PromoCode;
use App\Models\Template;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function __construct(protected TelegramService $telegramService)
    {}

    // =========================================================================
    // Dashboard
    // =========================================================================

    public function dashboard()
    {
        return view('admin.dashboard');
    }

    /**
     * Stats API endpoint
     */
    public function stats()
    {
        $totalRevenue = Payment::where('status', 'paid')->sum(
            DB::raw('amount - discount_amount')
        );

        $paidDocCount = GeneratedDocument::where('status', 'paid')->count();

        $topTemplates = Template::withCount(['generatedDocuments' => function ($q) {
                $q->where('status', 'paid');
            }])
            ->orderByDesc('generated_documents_count')
            ->limit(5)
            ->get(['id', 'name', 'slug', 'generated_documents_count']);

        $dailyRevenue = Payment::where('status', 'paid')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(amount - discount_amount) as revenue, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'success'       => true,
            'total_revenue' => $totalRevenue,
            'paid_doc_count' => $paidDocCount,
            'top_templates' => $topTemplates,
            'daily_revenue' => $dailyRevenue,
        ]);
    }

    // =========================================================================
    // Templates
    // =========================================================================

    public function templatesList()
    {
        $templates = Template::with('category')
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->get();

        return response()->json(['success' => true, 'data' => $templates]);
    }

    public function templateCreate(Request $request)
    {
        $validated = $request->validate([
            'category_id'      => ['required', 'exists:categories,id'],
            'name'             => ['required', 'string', 'max:255'],
            'description'      => ['required', 'string'],
            'price'            => ['required', 'numeric', 'min:0'],
            'schema'           => ['required', 'array'],
            'definition'       => ['required', 'array'],
            'sort_order'       => ['integer', 'min:0'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $template = Template::create($validated);

        return response()->json(['success' => true, 'data' => $template]);
    }

    public function templateUpdate(Request $request, int $id)
    {
        $template = Template::findOrFail($id);

        $validated = $request->validate([
            'name'             => ['sometimes', 'string', 'max:255'],
            'description'      => ['sometimes', 'string'],
            'price'            => ['sometimes', 'numeric', 'min:0'],
            'schema'           => ['sometimes', 'array'],
            'definition'       => ['sometimes', 'array'],
            'sort_order'       => ['sometimes', 'integer', 'min:0'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);

        $template->update($validated);

        return response()->json(['success' => true, 'data' => $template->fresh()]);
    }

    public function templateToggle(int $id)
    {
        $template = Template::findOrFail($id);
        $template->update(['is_active' => !$template->is_active]);

        AuditLog::create([
            'user_id'      => Auth::id(),
            'action'       => $template->is_active ? 'template.activated' : 'template.deactivated',
            'subject_type' => 'Template',
            'subject_id'   => $id,
            'payload'      => ['name' => $template->name],
            'ip_address'   => request()->ip(),
        ]);

        return response()->json([
            'success'   => true,
            'is_active' => $template->is_active,
        ]);
    }

    // =========================================================================
    // Library
    // =========================================================================

    public function libraryList()
    {
        $items = LibraryItem::orderByDesc('created_at')->paginate(20);
        return response()->json(['success' => true, 'data' => $items]);
    }

    public function libraryUpload(Request $request)
    {
        $request->validate([
            'file'        => ['required', 'file', 'max:20480'], // 20MB max
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category'    => ['nullable', 'string', 'max:100'],
            'is_free'     => ['boolean'],
        ]);

        $uploadedFile = $request->file('file');
        $localPath = $uploadedFile->getPathname();
        $caption = $request->title . ' — Kenya Docs Library';

        // Upload to Telegram
        $fileId = $this->telegramService->uploadFile($localPath, $caption);

        $item = LibraryItem::create([
            'title'            => $request->title,
            'description'      => $request->description,
            'file_name'        => $uploadedFile->getClientOriginalName(),
            'telegram_file_id' => $fileId,
            'file_size'        => $uploadedFile->getSize(),
            'mime_type'        => $uploadedFile->getMimeType(),
            'category'         => $request->category,
            'is_free'          => $request->boolean('is_free', true),
            'is_active'        => true,
        ]);

        return response()->json(['success' => true, 'data' => $item]);
    }

    public function libraryUpdate(Request $request, int $id)
    {
        $item = LibraryItem::findOrFail($id);

        $validated = $request->validate([
            'title'       => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'category'    => ['nullable', 'string', 'max:100'],
            'is_free'     => ['boolean'],
            'is_active'   => ['boolean'],
        ]);

        $item->update($validated);

        return response()->json(['success' => true, 'data' => $item->fresh()]);
    }

    public function libraryDelete(int $id)
    {
        $item = LibraryItem::findOrFail($id);
        $item->delete();

        return response()->json(['success' => true]);
    }

    // =========================================================================
    // Payments
    // =========================================================================

    public function paymentsList(Request $request)
    {
        $query = Payment::with(['generatedDocument.template', 'user'])
            ->orderByDesc('created_at');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->date_from) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $payments = $query->paginate(20);

        return response()->json(['success' => true, 'data' => $payments]);
    }

    public function paymentUnlock(int $id)
    {
        $payment = Payment::findOrFail($id);
        $doc = $payment->generatedDocument;

        if ($doc) {
            $doc->update([
                'status'                 => 'paid',
                'edit_window_expires_at' => now()->addHours(24),
            ]);
        }

        AuditLog::create([
            'user_id'      => Auth::id(),
            'action'       => 'payment.unlocked',
            'subject_type' => 'Payment',
            'subject_id'   => $id,
            'payload'      => [],
            'ip_address'   => request()->ip(),
        ]);

        return response()->json(['success' => true]);
    }

    // =========================================================================
    // Users
    // =========================================================================

    public function usersList(Request $request)
    {
        $query = User::withCount('generatedDocuments')->orderByDesc('created_at');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('phone', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('name', 'LIKE', '%' . $request->search . '%');
            });
        }

        $users = $query->paginate(20);

        return response()->json(['success' => true, 'data' => $users]);
    }

    public function userDelete(Request $request, int $id)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $user = User::findOrFail($id);

        DB::transaction(function () use ($user, $request) {
            // Anonymise payment records
            $user->payments()->update(['user_id' => null, 'phone' => null]);

            // Delete related data
            foreach ($user->generatedDocuments as $doc) {
                $doc->downloads()->delete();
                $doc->delete();
            }

            $user->profile()->delete();
            DB::table('promo_code_uses')->where('user_id', $user->id)->delete();

            AuditLog::create([
                'user_id'      => Auth::id(),
                'action'       => 'admin.user.deleted',
                'subject_type' => 'User',
                'subject_id'   => $user->id,
                'payload'      => [
                    'phone'  => $user->phone,
                    'reason' => $request->reason,
                ],
                'ip_address'   => request()->ip(),
            ]);

            $user->delete();
        });

        return response()->json(['success' => true]);
    }

    // =========================================================================
    // Promo Codes
    // =========================================================================

    public function promoList()
    {
        $promos = PromoCode::withCount('promoCodeUses')->orderByDesc('created_at')->get();
        return response()->json(['success' => true, 'data' => $promos]);
    }

    public function promoCreate(Request $request)
    {
        $validated = $request->validate([
            'code'              => ['sometimes', 'string', 'max:50'],
            'type'              => ['required', 'in:percent,fixed'],
            'value'             => ['required', 'numeric', 'min:0'],
            'max_uses'          => ['nullable', 'integer', 'min:1'],
            'expires_at'        => ['nullable', 'date', 'after:now'],
            'is_referral'       => ['boolean'],
            'referrer_user_id'  => ['nullable', 'exists:users,id'],
        ]);

        // Auto-generate code if not provided
        if (empty($validated['code'])) {
            $validated['code'] = strtoupper(Str::random(8));
        }

        $validated['code']      = strtoupper($validated['code']);
        $validated['is_active'] = true;

        $promo = PromoCode::create($validated);

        return response()->json(['success' => true, 'data' => $promo]);
    }

    public function promoToggle(int $id)
    {
        $promo = PromoCode::findOrFail($id);
        $promo->update(['is_active' => !$promo->is_active]);

        return response()->json(['success' => true, 'is_active' => $promo->is_active]);
    }

    public function promoDelete(int $id)
    {
        $promo = PromoCode::findOrFail($id);
        $promo->delete();

        return response()->json(['success' => true]);
    }

    // =========================================================================
    // Missing document requests
    // =========================================================================

    public function missingDocumentsList()
    {
        $requests = DB::table('missing_document_requests')
            ->selectRaw('document_description, search_query, COUNT(*) as count, MAX(created_at) as last_at')
            ->groupBy('document_description', 'search_query')
            ->orderByDesc('count')
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $requests]);
    }
}
