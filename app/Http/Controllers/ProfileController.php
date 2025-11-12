<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\User; // <-- TAMBAHKAN INI (Wajib untuk model Anda)

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
    
    // =========================================================
    // METHOD BARU UNTUK FIREBASE FCM TOKEN
    // =========================================================
    /**
     * Menyimpan FCM Token yang dikirim dari browser ke database user yang sedang login.
     * Method ini mengatasi error Route [save.fcm.token] not defined.
     */
    public function saveToken(Request $request)
    {
        // 1. Validasi Token
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        // 2. Ambil User yang sedang login
        $user = Auth::user(); 

        if ($user) {
            // 3. Simpan Token ke kolom fcm_token (yang baru saja Anda migrasi)
            $user->fcm_token = $request->fcm_token;
            $user->save();

            // Respon sukses ke JavaScript (Axios)
            return response()->json(['success' => true, 'message' => 'FCM Token berhasil disimpan.'], 200);
        }

        return response()->json(['success' => false, 'message' => 'Pengguna tidak terotentikasi.'], 401);
    }
}