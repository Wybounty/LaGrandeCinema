<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSpectateur
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role !== 'spectateur') {
            abort(403, 'Cette section est réservée aux spectateurs.');
        }

        return $next($request);
    }
}
