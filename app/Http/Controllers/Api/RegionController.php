<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

//Region untuk Endpoint
class RegionController extends Controller
{
    public function index(){
        $regions = Region::all();
        foreach($regions as $row){
            $data[] = [
                'code' => $row->code,
                'name' => $row->name,
            ];
        }

        $response = [
            'status'  => 200,
            'message' => 'success',
            'data'    => $data,
        ];

        return response()->json($response, 200);
    }

    public function getAreaBulk(Request $request, $provinceCode='', $cityCode=''){
        $cek_token_user = User::where('api_token',$request->bearerToken())->first();

        if($cek_token_user){
        if($cek_token_user && $request->bearerToken()){
            $offset = $request->query('offset', 0);
            $limit  = $request->query('limit', 100); 

            $provinces = Region::whereRaw("CHAR_LENGTH(code) = 2")->where("code","LIKE","{$provinceCode}%")->get();
            
            foreach($provinces as $row1){
                $cities    = Region::whereRaw("CHAR_LENGTH(code) = 5")->where("code","LIKE","{$row1->code}%")->where("code","LIKE","{$cityCode}%")->get();
                foreach($cities as $row2){
                    $districts = Region::whereRaw("CHAR_LENGTH(code) = 8")->where('code','LIKE',"{$row2->code}%")->get();
                    foreach($districts as $row3){
                        $temp_data[] = [
                            'province_code' => $row1->code,
                            'province_name' => $row1->name,
                            'city_code'     => $row2->code,
                            'city_name'     => $row2->name,
                            'district_code' => $row3->code,
                            'district_name' => $row3->name,
                        ];
                    }
                }
            }

            //offset dan limit di sini karena saat foreach masih generate data lengkapnya
            $data = array_slice($temp_data, $offset, $limit, true);
            $response = [
                'status'     => 200,
                'message'    => 'success',
                'total_data' => count($data),
                'data'       => $data,
            ];
        }else{
            $response = [
                'status'    => 401,
                'message'   => 'Token tidak ditemukan'
            ];
        }
        return response()->json($response);
    }
    
    public function getAllProvinces(){
        $provinces = Region::whereRaw("CHAR_LENGTH(code) = 2")->get();
        foreach($provinces as $row){
            $data[] = [
                'code' => $row->code,
                'name' => $row->name,
            ];
        }

        $response = [
            'status'     => 200,
            'message'    => 'success',
            'total_data' => count($data),
            'data'       => $data,
        ];

        return response()->json($response, 200);
    }

    public function getProvince($code){
        $province = Region::whereRaw("CHAR_LENGTH(code) = 2")->where('code', base64_decode($code))->first();
        return response()->json($province);
    }

    public function getAllCities(){
        $cities = Region::whereRaw("CHAR_LENGTH(code) = 5")->get();
        foreach($cities as $row){
            $data[] = [
                'code' => $row->code,
                'name' => $row->name,
            ];
        }
        return response()->json($data, 200);
    }

    public function getCityByProvince($parentCode){
        if(strlen($parentCode) != 2){
            return response()->json("Kode Provinsi tidak valid, lihat daftar Provinsi", 200);
        }
        $cities = Region::whereRaw("CHAR_LENGTH(code) = 5")->where("code","LIKE","{$parentCode}%")->get();
        
        foreach($cities as $row){
            $data[] = [
                'code' => $row->code,
                'name' => $row->name,
            ];
        }
        return response()->json($data, 200);
    }

    public function getCity($code){
        if(strlen($code) != 2){
            return response()->json("Kode Kota tidak valid, lihat daftar Kota", 200);
        }
        $city = Region::whereRaw("CHAR_LENGTH(code) = 5")->where('code', base64_decode($code))->first();
        return response()->json($city);
    }

    public function getAllDistricts(){
        $districts = Region::whereRaw("CHAR_LENGTH(code) = 8")->get();
        foreach($districts as $row){
            $data[] = [
                'code' => $row->code,
                'name' => $row->name,
            ];
        }
        return response()->json($data, 200);
    }

    public function getDistrictByCity($parentCode){
        if(strlen($parentCode) != 5){
            return response()->json("Kode Kota tidak valid, lihat daftar Kota", 200);
        }
        $districts = Region::whereRaw("CHAR_LENGTH(code) = 8")->where('code','LIKE',"{$parentCode}%")->get();

        foreach($districts as $row){
            $data[] = [
                'code' => $row->code,
                'name' => $row->name,
            ];
        }
        return response()->json($data, 200);
    }
    
    public function getDistrict($code){
        if(strlen($code) != 2){
            return response()->json("Kode Kecamatan tidak valid, lihat daftar Kecamatan", 200);
        }

        $district = Region::whereRaw("CHAR_LENGTH(code) = 8")->where('code', base64_decode($code))->first();
        return response()->json($district);
    }
}
