<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Tambahkan ini

class RoleAccessCheck
{
    public function handle(Request $request, Closure $next, $role)
    {
        // Pastikan user sudah login dan memiliki peran yang sesuai
        if (!Auth::check() || Auth::user()->peran !== $role) {
            abort(403, 'AKSES DITOLAK.');
        }

        return $next($request);
    }
}
