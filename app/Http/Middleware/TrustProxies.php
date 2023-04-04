<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array|string|null
     */
    protected $proxies;

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    // public function handle($request, Closure $next)
    // {
    //     $response = $next($request);

    //     $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    //     $response->headers->set('Pragma', 'no-cache');
    //     $response->headers->set('Expires', 'Sat, 05 Jan 2030 00:00:00 GMT');

    //     return $response;
    // }

}
