<?php

namespace App\Http\Middleware;

use Closure;

class CheckApprovedUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(config('config.USER_DEFAULT_INACTIVE') && auth()->user() != null && !auth()->user()->isSuperAdmin()){            
            if (empty(auth()->user()->approved_at)) {
                \Auth::logout();                    
                $output = ['errors' => __("general.have_to_active")];
                return redirect()->route('login');
            }
        }        

        return $next($request);
    }
}
