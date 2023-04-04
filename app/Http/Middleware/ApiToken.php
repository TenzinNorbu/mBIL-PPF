<?php 

namespace App\Http\Middleware;

use Closure;

class ApiToken
{
    public function handle($request, Closure $next)
	{
		$bearer = 'OGXR803wzEbKBYZVEu0M59kqhy68z6';
		
		if ($request->bearerToken() != $bearer) {
			return response()->json(['responseCode'=>'201', 'data' => null, 'message' => 'Unauthorized access']);
		}
		
		return $next($request);
	}
}