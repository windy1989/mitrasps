<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Weight;
use App\Models\AttendanceTemp;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\WeightHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class HomeController extends Controller
{
    public function login(Request $request) {

        $validator = Validator::make($request->all(), [ 
            'email' => 'required',
            'password' => 'required',
        ], [
            'email.required'       => 'Email pengguna tidak boleh kosong.',
            'password.required'     => 'Password pengguna tidak boleh kosong.',
        ]);

        if ($validator->fails()) { 
            return response()->json([
                'status' => 422,
                'error'  => $validator->errors()
            ]);
        }

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $token = auth()->user()->createApiToken(); #Generate token
            return response()->json(['status' => 'Authorised', 'token' => $token ], 200);
        } else { 
            return response()->json([
                'status'    => 401,
                'message'   => 'User tidak ditemukan.',
            ]);
        }
    }
}