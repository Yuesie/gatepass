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
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'jabatan_terpilih' => ['required', 'string', 'max:255'], // Menggunakan jabatan_terpilih dari form
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'jabatan_terpilih.required' => 'Jabatan / Peran wajib dipilih.',
        ]);

        // 1. PETA JABATAN KE PERAN (ROLE) SISTEM
        $roleFinal = $this->mapJabatanToRole($request->jabatan_terpilih);

        // 2. BUAT PENGGUNA BARU DENGAN PERAN DAN JABATAN
        $user = User::create([
            'name' => $request->jabatan_terpilih, // Menggunakan Jabatan sebagai Nama Tampilan sementara
            'email' => $request->email,
            'peran' => $roleFinal, // Menggunakan Peran hasil mapping
            'jabatan_default' => $request->jabatan_terpilih, // Menyimpan teks Jabatan di kolom ini
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }

    /**
     * Helper: Memetakan Jabatan yang dipilih pengguna ke Peran (Role) di sistem.
     * Logika ini harus sinkron dengan AdminController dan IzinMasukController.
     */
    protected function mapJabatanToRole(string $jabatan): string
    {
        // Menentukan peran berdasarkan jabatan terpilih
        return match ($jabatan) {
            // Approver L1 (Atasan Pemohon)
            'Spv II MPS', 'SPV II HSSE & FS', 'Sr Spv RSD', 'Spv I QQ', 'Spv I SSGA' => 'atasan_pemohon',
            
            // Approver L2 (Security)
            'Jr Assistant Security TNI/POLRI' => 'security',
            
            // Approver L3 (Manager)
            'IT Manager Banjarmasin', 'Pjs IT Manager Banjarmasin' => 'manager',
            
            // Administrasi / Pemohon Khusus
            'Admin' => 'admin',
            
            // Default Role (Pemohon, termasuk Kontraktor)
            default => 'pemohon',
        };
    }
}