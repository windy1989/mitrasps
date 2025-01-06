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
        ]);

        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 401);
        }

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $token = auth()->user()->createApiToken(); #Generate token
            return response()->json(['status' => 'Authorised', 'token' => $token ], 200);
        } else { 
            return response()->json(['status'=>'Unauthorised'], 401);
        }
    }

    public function updateWeight(Request $request) {
        $cek = User::where('api_token',$request->bearerToken())->count();

        if($cek > 0){
            $weight = Weight::where('place_id',$request->place_id)->first();
            if($weight){
                $weight->update([
                    'code'      => Str::random(25),
                    'place_id'  => $request->place_id,
                    'nominal'   => str_replace(',','',$request->nominal),
                    'rawdata'   => $request->rawdata,
                ]);
            }else{
                $weight = Weight::create([
                    'code'      => Str::random(25),
                    'place_id'  => $request->place_id,
                    'nominal'   => str_replace(',','',$request->nominal),
                    'rawdata'   => $request->rawdata,
                ]);
            }
            $now = date('Y-m-d H:i');
            $updateHistory = WeightHistory::where('created_at','like',"$now%")->first();
            if(!$updateHistory){
                WeightHistory::create([
                    'nominal'   => str_replace(',','',$request->nominal),
                    'rawdata'   => $request->rawdata,
                ]);
            }
            return response()->json(['status' => 'success'], 200);
        }else{
            return response()->json(['status' => 'failed'], 401);
        }
    }

    public function updateAttendance(Request $request) {
       
            $count = 0;
            $start_time = microtime(true);
           
            $collection = [];
            if($request->arrdata){
                $collection = collect($request->arrdata)/* ->filter(function ($item) {
                    return false !== stripos($item['recordTime'], '2023-07');
                }) */;
                foreach($collection as $row){
                    AttendanceTemp::create([
                        'code'          => $row['userSn'],
                        'user_id'       => $row['deviceUserId'],
                        'verify_type'   => $row['verifyType'],
                        'record_time'   => $row['recordTime'],
                        'machine_id'    => $request->machine_id,
                    ]);
                    $count++;
                }
            }

            $end_time = microtime(true);
        
            $execution_time = ($end_time - $start_time);

            return response()->json(['status' => 'success','processed_data' => $count, 'time' => $execution_time], 200);

    }

    public function getAttendance(Request $request) {

        if($request->machine){
            $all = AttendanceTemp::where('machine_id',$request->machine)->get();
        }else{
            $all = AttendanceTemp::all();
        }
        return response()->json(['status' => 'success','processed_data' => $all->count(), 'data' => $all], 200);
    }

    public function getStatusUser(Request $request) {
        $cek = User::where('api_token',$request->bearerToken())->count();
        if($cek > 0){
            $user = User::where('employee_no',$request->nik)->first();
            if($user){
                return response()->json(['status' => 'success','status_user' => $user->statusRaw()], 200);
            }else{
                return response()->json(['status' => 'failed', 'message' => 'user not found'], 401);
            }
        }else{
            return response()->json(['status' => 'failed'], 401);
        }
    }

    public function updateStatusUser(Request $request) {
        $cek = User::where('api_token',$request->bearerToken())->count();
        if($cek > 0){
            $user = User::where('employee_no',$request->nik)->first();
            if($user){
                if(in_array($request->status,['1','2'])){
                    $user->update([
                        'status'    => $request->status,
                    ]);
                    activity()
                        ->performedOn(new User())
                        ->withProperties([
                            'nik'       => $request->nik,
                            'status'    => $request->status,
                        ])
                        ->log('Add / edit user via api update status.');
                    return response()->json(['status' => 'success'], 200);
                }else{
                    return response()->json(['status' => 'accepted value status 1 (active) or 2 (inactive)'], 500);
                }
            }else{
                return response()->json(['status' => 'failed', 'message' => 'user not found'], 401);
            }
        }else{
            return response()->json(['status' => 'failed'], 401);
        }
    }
}