<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\GeneratedDocument;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct(protected StorageService $storageService) {}

    /**
     * Delete a user and all their data (H3 + ODPC-compliant)
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $user = Auth::user();

        DB::transaction(function () use ($user, $request) {
            // Delete document files from storage first
            foreach ($user->generatedDocuments as $doc) {
                foreach (['preview_path', 'pdf_path', 'word_path'] as $field) {
                    if ($doc->$field) {
                        $this->storageService->delete($doc->$field);
                    }
                }
                $doc->downloads()->delete();
                // Nullify payments FK before deleting (payments.generated_document_id)
                // Payments are kept for accounting; document_id is set null
                $doc->payments()->update(['generated_document_id' => null]);
                $doc->delete();
            }

            // Anonymise payment records: nullify user link and phone (column is nullable)
            $user->payments()->update([
                'user_id' => null,
                'phone'   => null,
            ]);

            // Delete profile
            $user->profile()->delete();

            // Delete promo code uses
            DB::table('promo_code_uses')->where('user_id', $user->id)->delete();

            // H3: Hash phone in audit log — do not store plaintext PII
            AuditLog::create([
                'user_id'      => null,
                'action'       => 'user.self_deleted',
                'subject_type' => 'User',
                'subject_id'   => $user->id,
                'payload'      => [
                    'phone_hash' => hash('sha256', $user->phone), // hashed, not plain
                    'reason'     => $request->reason,
                ],
                'ip_address'   => $request->ip(),
            ]);

            $user->delete();
        });

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Your account and personal data have been permanently deleted.',
        ]);
    }
}
