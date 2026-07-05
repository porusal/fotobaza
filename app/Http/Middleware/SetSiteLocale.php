<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSiteLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // Google Translate needs one consistent source language in the DOM.
        // The selected target language is stored separately in cookies.
        app()->setLocale('ru');

        return $next($request);
    }
}
