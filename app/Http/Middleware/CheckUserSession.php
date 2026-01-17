<?php

namespace App\Http\Middleware;


use Symfony\Component\HttpFoundation\Response;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckUserSession
{
   // public function handle(Request $request, Closure $next)
    //{
     //   if (session()->has('id')) {

      //      $user = DB::table('users')->where('id', session('id'))->first();

      //      if (!$user) {
         //       session()->flush();
         //       return redirect()->route('login');
         //   }

            // ❌ Another device login
          //  if ($user->session_id !== session()->getId()) {
          //      session()->flush();
          //      return redirect()->route('login')
          //          ->with('msg', 'Logged out due to login from another device.');
          //  }

            // ❌ Password changed
         //   if (
         //       session('password_changed_at') &&
         //       $user->password_changed_at &&
         //       session('password_changed_at') != $user->password_changed_at
          //  ) {
          //      session()->flush();
         //       return redirect()->route('login')
          //          ->with('msg', 'Password changed. Please login again.');
         //   }
       // }

      //  return $next($request);
    //}
	
	public function handle(Request $request, Closure $next)
    {
        if (session()->has('id')) {

            $user = DB::table('users')
                ->where('id', session('id'))
                ->first();

            // ❌ User not found
            if (!$user) {
                session()->flush();
                return redirect()->route('login');
            }

            // ❌ Agent blocked by admin (LIVE LOGOUT)
            
			if ($user->role_id == 4 && $user->status == 0) {
    session()->flush();
    return redirect()->route('login')->with([
        'msg' => 'Your account has been blocked by admin.',
        'msg_type' => 'danger'
    ]);
}


            // ❌ Another device login
            if ($user->session_id !== session()->getId()) {
                session()->flush();
                return redirect()->route('login')
                    ->with('msg', 'You have been logged out due to login from another device.');
            }

            // ❌ Password changed
            if (
                session('password_changed_at') &&
                $user->password_changed_at &&
                session('password_changed_at') != $user->password_changed_at
            ) {
                session()->flush();
                return redirect()->route('login')
                    ->with('msg', 'Password changed. Please login again.');
            }
        }

        return $next($request);
    }
}