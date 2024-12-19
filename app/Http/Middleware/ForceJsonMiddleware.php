<?php

namespace App\Http\Middleware;

use Closure;

class ForceJsonMiddleware
{
    public function handle($request, Closure $next)
    {
        // Check if `Content-Type` is missing or not `application/json`
        if (!$request->isJson() && empty($request->header('Content-Type'))) {
            $input = json_decode($request->getContent(), true);

            // If the body is valid JSON, replace the request's input
            if (json_last_error() === JSON_ERROR_NONE) {
                $request->merge($input);
            }
        }

        return $next($request);
    }
}
