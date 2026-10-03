<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use App\Models\Order;

Route::get('/', function () {
    return view('welcome');
});


// google webhook
Route::post('/google-calendar/webhook', function (Request $request) {

    if (!$request->hasHeader('X-Goog-Channel-ID')) {
        return response('Unauthorized', 403);
    }

    $lockKey = 'gc_sync_lock';

    if (Cache::has($lockKey)) {
        return response('Locked', 200);
    }

    Cache::put($lockKey, true, 10);

    Artisan::call('app:sync-google-calendar');

    return response('OK', 200);

})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);


// payment success page
Route::get('/payment/success/{order}', function ($orderId) {

    $order = Order::find($orderId);

    if (!$order) {
        abort(404);
    }

    return view('payment-successs', compact('order'));

});

Route::get('/php-upload-info', function () {
    return response()->json([
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'max_input_time' => ini_get('max_input_time'),
    ]);
});
