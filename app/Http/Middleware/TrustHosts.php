<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     *
     * @return array
     */
    protected $headers = [
        Request::HEADER_USER_AGENT,
        Request::HEADER_X_FORWARDED_FOR,
        Request::HEADER_X_FORWARDED_HOST,
        Request::HEADER_X_FORWARDED_PORT,
        Request::HEADER_X_FORWARDED_PROTO,
        Request::HEADER_X_REQUESTED_WITH,
    ];

    public function hosts()
    {
        return [
            $this->allSubdomainsOfApplicationUrl(),
        ];
    }
}
