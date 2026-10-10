<?php

namespace App\Http\Controllers;

use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class ProfileController extends Controller
{
    /**
     * Show profile edit form
     */
    public function edit()
    {
        $user = Auth::user();
        $profile = $user->profile;

        // Decrypt ID number for display (only owner can see)
        if ($profile && $profile->id_number_encrypted) {
            try {
                $profile->id_number = Crypt::decryptString($profile->id_number_encrypted);
            } catch (\Exception $e) {
                $profile->id_number = null;
            }
        }

        return view('profile', compact('user', 'profile'));
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['nullable', 'email', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'id_number' => ['nullable', 'string', 'max:20'],
            // M6: phone must be unique (ignore current user) and accept 07/01
            'phone'     => ['required', 'regex:/^(07|01)\d{8}$/', 'unique:users,phone,' . $user->id],
            'address'   => ['nullable', 'string', 'max:500'],
            'city'      => ['nullable', 'string', 'max:100'],
        ]);

        // Update user
        $user->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        // Encrypt ID number before storing
        $idNumberEncrypted = null;
        if (!empty($validated['id_number'])) {
            $idNumberEncrypted = Crypt::encryptString($validated['id_number']);
        }

        // Update or create profile
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name'            => $validated['full_name'],
                'id_number_encrypted'  => $idNumberEncrypted,
                'phone'                => $validated['phone'],
                'address'              => $validated['address'],
                'city'                 => $validated['city'],
            ]
        );

        return back()->with('success', 'Profile updated successfully.');
    }
}
