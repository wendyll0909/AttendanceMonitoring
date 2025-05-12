<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogHtmxResponse
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->header('HX-Request')) {
            Log::debug('HTMX Response Debug', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'content_type' => $response->headers->get('Content-Type'),
                'content' => substr($response->getContent(), 0, 1000), // Limit to 1000 chars
                'status' => $response->status(),
            ]);
        }

        return $response;
    }
}