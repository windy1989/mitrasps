<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;


class DefaultController extends Controller
{
    public function index() {
        return view('welcome2');
    }
}