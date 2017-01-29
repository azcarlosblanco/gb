<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\HeaderBag;

class AddJsonAcceptHeader
{
    /**
     * Add Json HTTP_ACCEPT header for an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $request->server->set('HTTP_ACCEPT', 'application/json');
        $request->headers = new HeaderBag($request->server->getHeaders());
        return $next($request);
    }
}