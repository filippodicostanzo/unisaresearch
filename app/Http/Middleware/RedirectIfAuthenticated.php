<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null ...$guards
     * @return mixed
     */

    /*
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }*/

    public function handle($request, Closure $next, $guard = null)
    {


        if (Auth::guard($guard)->check()) {
            $user = Auth::user();

            if ($user->hasRole('user')) {
                return redirect('/guest');
            }

            if ($user->hasRole('researcher')) {
                return redirect('/home');
            }

            if ($user->hasRole('administrator|superadministrator')) {
                return redirect('/admin');
            }
        }
        return $next($request);
    }
}
