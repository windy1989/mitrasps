<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MitraMarketingOrderController;
use App\Http\Controllers\Api\HomeController;

Route::post('login',[HomeController::class, 'login']);
Route::prefix('mitra_marketing_order')->group(function () {
    Route::post('create', [MitraMarketingOrderController::class, 'create']);
});