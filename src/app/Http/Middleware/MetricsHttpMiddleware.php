<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Support\Metrics;
use Illuminate\Http\Response;

class MetricsHttpMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);
        $route = optional($request->route())->getName() ?? ($request->route()?->uri() ?? 'unknown');
        $method = $request->getMethod();

        try {
            $response = $next($request);
            $status = (string) $response->getStatusCode();
            return $response;
        } catch (\Throwable $e) {
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
            throw $e;
        } finally {
            $registry = Metrics::registry();
            $ns = env('PROM_NAMESPACE', 'chatpulse');

            // Counter: http_requests_total
            $counter = $registry->getOrRegisterCounter($ns, 'http_requests_total', 'HTTP requests', ['route','method','status']);
            $counter->inc([$route, $method, $status]);

            // Histogram: http_request_duration_seconds
            $counter = $registry->getOrRegisterCounter($ns, 'http_requests_total', 'HTTP requests', ['route','method','status']);
            $counter->inc([$route, $method, $status]);

            $hist = $registry->getOrRegisterHistogram($ns, 'http_request_duration_seconds', 'HTTP request duration', ['route','method','status'], [0.01,0.025,0.05,0.1,0.25,0.5,1,2]);
            $hist->observe(microtime(true) - $start, [$route, $method, $status]);
        }
    }
}
