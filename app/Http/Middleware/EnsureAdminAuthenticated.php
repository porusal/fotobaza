<?php

namespace App\Http\Middleware;

use App\Support\AdminSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (AdminSession::currentUser($request)) {
            return $next($request);
        }

        if (AdminSession::pendingUser($request)) {
            return redirect()->route('admin.2fa.challenge');
        }

        AdminSession::storeIntendedUrl($request);

        return redirect()
            ->route('admin.login')
            ->with('status', 'Сначала войдите в панель управления.');
    }
}
