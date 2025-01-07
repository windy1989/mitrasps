<?php

use App\Http\Controllers\Web\DefaultController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DefaultController::class, 'index']);