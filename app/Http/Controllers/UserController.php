<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Delete a user and all related data (ODPC-compliant)
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $user = Auth::user();

        DB::transaction(function () use ($user, $request) {
            // Anonymise payment records (keep for accounting)
            $user->payments()->update([
                'user_id' => null,
                'phone'   => null,
            ]);

            // Delete generated documents and related data
            foreach ($user->generatedDocuments as $doc) {
                $doc->downloads()->delete();
                $doc->delete();
            }

            // Delete profile
            $user->profile()->delete();

            // Delete promo code uses
            DB::table('promo_code_uses')->where('user_id', $user->id)->delete();

            // Log deletion
            AuditLog::create([
                'user_id'      => null,
                'action'       => 'user.deleted',
                'subject_type' => 'User',
                'subject_id'   => $user->id,
                'payload'      => [
                    'phone'  => $user->phone,
                    'reason' => $request->reason,
                ],
                'ip_address'   => $request->ip(),
            ]);

            // Delete user
            $user->delete();
        });

        // Logout
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Your account and data have been deleted permanently.',
        ]);
    }
}
