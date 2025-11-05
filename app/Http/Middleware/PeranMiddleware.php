<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PeranMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $peran): Response
    {
        // 1. Pastikan pengguna terautentikasi
        if (!Auth::check()) {
            return redirect('login');
        }

        // 2. Ambil peran yang dimiliki pengguna
        $userPeran = Auth::user()->peran;
        
        // 3. Pisahkan peran yang diizinkan (jika ada beberapa, contoh: 'admin,security')
        $allowedRoles = explode(',', $peran);

        // 4. Periksa apakah peran pengguna ada di dalam daftar peran yang diizinkan
        if (!in_array($userPeran, $allowedRoles)) {
            // Jika tidak memiliki peran yang diizinkan, kembalikan ke dashboard atau tampilkan 403
           return redirect('/dashboard');
        }

        return $next($request);
    }
}