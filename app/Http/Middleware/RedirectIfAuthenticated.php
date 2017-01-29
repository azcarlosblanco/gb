<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\NovaMessage;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            $novaMessage=new NovaMessage();
            $novaMessage->setRoute('/');
            return response($novaMessage->toJSON(),200)
                            ->header('Content-Type', 'application/json');
        }
        return $next($request);
    }
}
