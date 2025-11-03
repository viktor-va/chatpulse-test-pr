<?php

use App\Events\MessagePublished;
use App\Support\Metrics;
use Illuminate\Support\Facades\Route;
use Modules\Chat\Models\Room;
use Prometheus\RenderTextFormat;

Route::get('/test/broadcast', function () {
    event(new MessagePublished('Hello from ChatPulse!'));
    return 'ok';
});

Route::view('/test/listen', 'test');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/demo', function () {
    return view('demo', [
        'roomId' => Room::where('name','general')->first()?->id,
        'token'  => env('DEMO_USER_TOKEN'),
    ]);
});

Route::get('/metrics', function () {
    $registry = Metrics::registry();
    $renderer = Metrics::renderer();
    $result = $renderer->render($registry->getMetricFamilySamples());
    return response($result, 200)->header('Content-Type', RenderTextFormat::MIME_TYPE);
});

Route::get('/fail', function () {
    abort(500, 'Synthetic failure for metrics test');
});


