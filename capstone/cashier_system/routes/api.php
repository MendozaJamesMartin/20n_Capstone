<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VerifyPaymentController;


Route::group(['middleware' => ['apikey', 'throttle:30,1']], function() {
    Route::post('/verify-payment', [VerifyPaymentController::class, 'verify']);
});