<?php

namespace App\Support;

use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\Redis;
use Prometheus\Storage\InMemory;

class Metrics
{
    public static function registry(): CollectorRegistry
    {
        static $registry = null;
        if ($registry) return $registry;

        if (app()->environment('testing') || env('PROM_STORAGE') === 'in_memory') {
            $registry = new CollectorRegistry(new InMemory(), false);
            return $registry;
        }

        $adapter = new Redis([
            'host' => env('PROM_REDIS_HOST', 'redis'),
            'port' => (int) env('PROM_REDIS_PORT', 6379),
            'timeout' => 0.1,
            'persistent_connections' => false,
        ]);

        $registry = new CollectorRegistry($adapter, false);
        return $registry;
    }

    public static function renderer(): RenderTextFormat
    {
        return new RenderTextFormat();
    }
}
