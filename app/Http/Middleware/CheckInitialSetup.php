<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInitialSetup
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isInitialized = User::exists();

        if (! $isInitialized && ! $request->is('init')) {
            return redirect('/init');
        }

        if ($isInitialized && $request->is('init')) {
            return redirect('/');
        }

        return $next($request);
    }
}
