<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MitraMarketingOrderController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\MitraCustomerController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\MitraComplaintSalesController;

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
    Route::post('/get_data', [StockController::class, 'getData']);
});

Route::prefix('tracking')->group(function () {
    Route::post('/get_data', [TrackingController::class, 'getData']);
});

Route::get('/all_area/{provinceCode?}/{cityCode?}', [RegionController::class, 'getAreaBulk']);

Route::prefix('province')->group(function (){
    Route::get('/', [RegionController::class, 'getAllProvinces']);
    Route::get('/{code}', [RegionController::class, 'getProvince']);
});
Route::prefix('city')->group(function (){
    Route::get('/', [RegionController::class, 'getAllCities']);
    Route::get('/filterProvince/{provinceCode}', [RegionController::class, 'getCityByProvince']);
    Route::get('/{code}', [RegionController::class, 'getCity']);
});
Route::prefix('district')->group(function (){
    Route::get('/', [RegionController::class, 'getAllDistricts']);
    Route::get('/filterCity/{cityCode}', [RegionController::class, 'getDistrictByCity']);
    Route::get('/{code}', [RegionController::class, 'getDistrict']);
    Route::get('/byName/{districtName?}', [RegionController::class, 'getDistrictByName']);
});

Route::prefix('customer')->group(function (){
    Route::get('/', [MitraCustomerController::class, 'getDataAll']);
    Route::get('/{code}', [MitraCustomerController::class, 'getData']);
    Route::post('/', [MitraCustomerController::class, 'create']);
    Route::put('/{code}', [MitraCustomerController::class, 'update']);
    // Route::put('/{code}/restore', [MitraCustomerController::class, 'restore']);
    Route::delete('/{code}', [MitraCustomerController::class, 'delete']);
});

Route::prefix('complaint')->group(function (){
    Route::get('/', [MitraComplaintSalesController::class, 'getDataAll']);
    Route::get('/{code}', [MitraComplaintSalesController::class, 'getData']);
    Route::post('/', [MitraComplaintSalesController::class, 'create']);
    // Route::put('/{code}', [MitraComplaintSalesController::class, 'update']);
    // Route::put('/{code}/restore', [MitraCustomerController::class, 'restore']);
    // Route::delete('/{code}', [MitraComplaintSalesController::class, 'delete']);
});