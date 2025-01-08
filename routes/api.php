<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MitraMarketingOrderController;
use App\Http\Controllers\Api\HomeController;

Route::post('login',[HomeController::class, 'login']);
Route::prefix('mitra_marketing_order')->group(function () {
    Route::post('/create', [MitraMarketingOrderController::class, 'create']);
    Route::put('/update', [MitraMarketingOrderController::class, 'update']);
    Route::get('/get_data', [MitraMarketingOrderController::class, 'getData']);
    Route::post('/get_data_all', [MitraMarketingOrderController::class, 'getDataAll']);
    Route::post('/destroy', [MitraMarketingOrderController::class, 'destroy']);
});