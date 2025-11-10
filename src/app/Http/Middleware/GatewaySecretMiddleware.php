<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GatewaySecretMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $secret = config('services.gateway.secret');

        if (!$secret) {
            // No secret configured → middleware disabled (monolith / local / tests by default)
            return $next($request);
        }

        // Expect header from trusted gateway
        $incoming = $request->header('X-Internal-Gateway-Secret');

        if (!hash_equals($secret, (string) $incoming)) {
            abort(403, 'Forbidden (gateway)');
        }

        return $next($request);
    }
}
