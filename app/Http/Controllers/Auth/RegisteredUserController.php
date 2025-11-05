<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Tampilkan halaman register.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Tangani pendaftaran pengguna baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'jabatan_terpilih' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $selectedJabatan = $request->jabatan_terpilih;
        $peranDasar = 'pembuat_gatepass'; // Default (pemohon umum)

        // ===============================
        // 🔹 Mapping Jabatan ke Peran Dasar
        // ===============================
        if (in_array($selectedJabatan, [
            'Spv II MPS',
            'SPV II HSSE & FS',
            'Sr Spv RSD',
            'Spv I QQ',
            'Spv I SSGA'
        ])) {
            $peranDasar = 'atasan_pemohon'; // Approver Level 1
        } elseif ($selectedJabatan === 'Admin') {
            $peranDasar = 'admin'; // Admin
        } elseif (in_array($selectedJabatan, [
            'Security',
            'Jr Assistant Security TNI/POLRI'
        ])) {
            $peranDasar = 'security'; // Approver Level 2
        } elseif (in_array($selectedJabatan, [
            'IT Manager Banjarmasin',
            'Pjs IT Manager Banjarmasin'
        ])) {
            $peranDasar = 'manager'; // Approver Level 3
        } elseif ($selectedJabatan === 'Kontraktor') {
            $peranDasar = 'kontraktor'; // 🔹 Tambahan untuk kontraktor
        }

        // ===============================
        // 🔹 Simpan ke Database
        // ===============================
        $user = User::create([
            'name' => 'User Baru - ' . $selectedJabatan,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'peran' => $peranDasar,
            'jabatan_default' => $selectedJabatan,
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
