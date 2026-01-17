<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class SessionTimeout
{
    protected $timeout = 300; // 5 minutes

    public function handle(Request $request, Closure $next)
    {
        if (session()->has('id')) {

            if (session()->has('last_activity')) {

                if ((time() - session('last_activity')) > $this->timeout) {
                    session()->flush();
                    return redirect()->route('login')
                        ->with('msg', 'Session expired due to inactivity.');
                }
            }

            session(['last_activity' => time()]);
        }

        return $next($request);
    }
}
