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
use Illuminate\Validation\Rule;
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
    // ... (method create) ...

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            // Input nama dihilangkan
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'jabatan_terpilih' => ['required', 'string', 'max:255'], // Input Jabatan dari dropdown
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        
        // app/Http/Controllers/Auth/RegisteredUserController.php (di dalam method store)

// ...

        $selectedJabatan = $request->jabatan_terpilih;
        $peranDasar = 'pembuat_gatepass'; // Default

        // Mapping Jabatan ke Peran Dasar
        if (in_array($selectedJabatan, ['Spv II MPS', 'SPV II HSSE & FS', 'Sr Spv RSD', 'Spv I QQ', 'Spv I SSGA'])) {
            $peranDasar = 'atasan_pemohon'; // Approver L1
        } elseif ($selectedJabatan === 'Admin') {
            $peranDasar = 'admin'; // Admin
        } elseif ($selectedJabatan === 'Security' || $selectedJabatan === 'Jr Assistant Security TNI/POLRI') {
            // Memasukkan jabatan Security baru ke peran 'security' (Approver L2)
            $peranDasar = 'security'; 
        } elseif (in_array($selectedJabatan, ['IT Manager Banjarmasin', 'Pjs IT Manager Banjarmasin'])) {
            // Memasukkan jabatan Manager/PJS ke peran 'manager' (Approver L3)
            $peranDasar = 'manager';
        }

// ...
        
        $user = User::create([
            'name' => 'User Baru - ' . $selectedJabatan, // Nama default
            'email' => $request->email,
            'password' => Hash::make($request->password),
            
            'peran' => $peranDasar,
            'jabatan_default' => $selectedJabatan, // Menyimpan jabatan detail
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
