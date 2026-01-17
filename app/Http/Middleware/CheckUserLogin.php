<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserLogin
{
	
    public function handle(Request $request, Closure $next)
    {
        // 🔴 User login nahi hai
        if (!session()->has('id')) {

            // login page ko allow karo
            if (
                $request->routeIs('login') ||
                $request->is('login') ||
                $request->is('/')
            ) {
                return $next($request);
            }

            return redirect()->route('login')
                ->with('msg_class', 'danger')
                ->with('msg', 'Please login to continue.');
        }

        // 🔥 OPTIONAL: session hijack protection
        if (session()->has('last_activity')) {
            if (time() - session('last_activity') > 300) { // 1 hour
                session()->flush();
                return redirect()->route('login')
                    ->with('msg_class', 'danger')
                    ->with('msg', 'Session expired. Please login again.');
            }
        }

        session(['last_activity' => time()]);

        return $next($request);
    }
}
