<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use App\Http\Controllers\TestBroadcastController;

/*
|--------------------------------------------------------------------------
| Test Routes
|--------------------------------------------------------------------------
|
*/

Route::get('/test-broadcast', [TestBroadcastController::class, 'showTestPage']);
Route::post('/api/test-broadcast', [TestBroadcastController::class, 'triggerTestBroadcast']);

Route::get('/test', function () {
    dd("test something");
});