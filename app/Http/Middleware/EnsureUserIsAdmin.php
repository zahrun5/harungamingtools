<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Blokir siapa pun yang bukan admin dengan 403 — dipakai buat semua
     * route /dev/* (dev tools, reels, verifikasi Albion, dll).
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        return $next($request);
    }
}
