<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MitraMarketingOrderController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\TrackingController;

Route::post('login',[HomeController::class, 'login']);
Route::prefix('mitra_marketing_order')->group(function () {
    Route::post('/create', [MitraMarketingOrderController::class, 'create']);
    Route::put('/update', [MitraMarketingOrderController::class, 'update']);
    Route::get('/get_data', [MitraMarketingOrderController::class, 'getData']);
    Route::post('/get_data_all', [MitraMarketingOrderController::class, 'getDataAll']);
    Route::post('/destroy', [MitraMarketingOrderController::class, 'destroy']);
});
Route::prefix('item_stock')->group(function () {
    Route::post('/get_data_all', [StockController::class, 'getDataAll']);
    Route::get('/get_data', [StockController::class, 'getData']);
});

Route::prefix('tracking')->group(function () {
    Route::get('/get_data', [TrackingController::class, 'getData']);
});