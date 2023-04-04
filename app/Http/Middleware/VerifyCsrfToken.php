<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */

    // protected $addHttpCookie = true;

	protected $except = [
	    // '/path',
	];

	// public function handle($request, Closure $next)
	// {
	//     header('Access-Control-Allow-Origin: https://ppf.com.bt');
	//     header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
	//     header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
	    
	//     return $next($request);
	// }

}
