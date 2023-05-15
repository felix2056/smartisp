<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redirect;

class LicenceExpire
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
        $licence = session()->get('licencia');

        if($licence == 'expired') {
            return Redirect::to('admin');
        }
        return $next($request);
    }
}
