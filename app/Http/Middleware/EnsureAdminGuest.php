<?php

namespace App\Http\Middleware;

use App\Support\AdminSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminGuest
{
    public function handle(Request $request, Closure $next): Response
    {
        if (AdminSession::currentUser($request)) {
            return redirect()->route('admin.dashboard');
        }

        if (AdminSession::pendingUser($request)) {
            return redirect()->route('admin.2fa.challenge');
        }

        return $next($request);
    }
}
