<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

// ── Cleanup old OTP codes (M16) ─────────────────────────────
Artisan::command('cleanup:otps', function () {
    $deleted = DB::table('otp_codes')
        ->where('expires_at', '<', now()->subHours(1))
        ->delete();
    $this->info("Deleted {$deleted} expired OTP records.");
    Log::info("cleanup:otps deleted {$deleted} records.");
})->purpose('Delete expired OTP codes older than 1 hour')->daily();

// ── Cleanup draft documents & preview files (M16) ───────────
Artisan::command('cleanup:drafts', function () {
    $cutoff = now()->subDays(3);

    $drafts = \App\Models\GeneratedDocument::where('status', 'draft')
        ->where('created_at', '<', $cutoff)
        ->get();

    $count = 0;
    foreach ($drafts as $doc) {
        foreach (['preview_path', 'pdf_path', 'word_path'] as $field) {
            if ($doc->$field && Storage::exists($doc->$field)) {
                Storage::delete($doc->$field);
            }
        }
        $doc->downloads()->delete();
        $doc->delete();
        $count++;
    }

    $this->info("Cleaned up {$count} stale draft documents.");
    Log::info("cleanup:drafts removed {$count} drafts.");
})->purpose('Remove draft documents older than 3 days')->daily();

// ── Cleanup old download logs (M16, Kenya DPA) ──────────────
Artisan::command('cleanup:downloads', function () {
    $deleted = DB::table('downloads')
        ->where('created_at', '<', now()->subDays(90))
        ->delete();
    $this->info("Deleted {$deleted} old download log entries.");
})->purpose('Purge download log entries older than 90 days')->weekly();

// ── Schedule definitions ─────────────────────────────────────
Schedule::command('cleanup:otps')->daily();
Schedule::command('cleanup:drafts')->daily();
Schedule::command('cleanup:downloads')->weekly();
