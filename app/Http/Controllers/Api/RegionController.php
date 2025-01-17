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

        return apiResponse(true, 200, 'Data area ditampilkan', $data, []);
        
    }

    public function getAreaBulk(Request $request, $provinceCode='', $cityCode=''){
        $cek = User::where('api_token',$request->bearerToken())->first();
        if($cek && $request->bearerToken()){
            //path param
            $provinceCode = base64_decode($provinceCode);
            $cityCode     = base64_decode($cityCode);

            //query param
            $offset = $request->query('offset', 0);
            $limit  = $request->query('limit', 100);

            if($provinceCode && Region::whereRaw("CHAR_LENGTH(code) = 2")->where("code","LIKE","{$provinceCode}")->count() < 1){
                return apiResponse(false, 400, 'Kode Provinsi tidak valid ', [], []);
            }

            if($cityCode && Region::whereRaw("CHAR_LENGTH(code) = 5")->where("code","LIKE","{$cityCode}")->count() < 1){
                return apiResponse(false, 400, 'Kode Kota tidak valid ', [], []);
            }

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

            if($data){
                return apiResponse(true, 200, 'Data area ditampilkan', $data, ['total_data' => count($data)]);
            }
            else{
                return apiResponse(true, 200, 'Data area tidak ditemukan', $data, []);
            }
        }else{
            return apiResponse(false, 401, 'Token tidak valid', null, []);
        }
    }
    
    //get all provinces
    public function getAllProvinces(Request $request){
        $cek = User::where('api_token',$request->bearerToken())->first();
        if($cek && $request->bearerToken()){
            $provinces = Region::whereRaw("CHAR_LENGTH(code) = 2")->get();
            foreach($provinces as $row){
                $data[] = [
                    'code' => $row->code,
                    'name' => $row->name,
                ];
            }

            if($data){
                return apiResponse(true, 200, 'Data provinsi ditampilkan', $data, ['total_data' => count($data)]);
            }
            else{
                return apiResponse(true, 200, 'Data provinsi tidak ditemukan', $data, []);
            }
        }else{
            return apiResponse(false, 401, 'Token tidak valid', null, []);
        }
    }

    //get single province
    public function getProvince(Request $request, $code){
        $cek = User::where('api_token',$request->bearerToken())->first();
        if($cek && $request->bearerToken()){
            //path param
            $code = base64_decode($code);

            $province = Region::whereRaw("CHAR_LENGTH(code) = 2")->where('code', $code)->first();
            if($province){
                $data = [
                    'code' => $province->code,
                    'name' => $province->name,
                ];
                return apiResponse(true, 200, 'Data provinsi ditampilkan', $data, []);
            }
            else{
                return apiResponse(true, 200, 'Data provinsi tidak ditemukan', [], []);
            }
        }
        else{
            return apiResponse(false, 401, 'Token tidak valid', null, []);
        }
    }

    //get all cities
    public function getAllCities(Request $request){
        $cek = User::where('api_token',$request->bearerToken())->first();
        if($cek && $request->bearerToken()){
            $cities = Region::whereRaw("CHAR_LENGTH(code) = 5")->get();
            foreach($cities as $row){
                $data[] = [
                    'code' => $row->code,
                    'name' => $row->name,
                ];
            }
            if($data){
                return apiResponse(true, 200, 'Data kota ditampilkan', $data, ['total_data' => count($data)]);
            }
            else{
                return apiResponse(true, 200, 'Data kota tidak ditemukan', $data, []);
            }
        }else{
            return apiResponse(false, 401, 'Token tidak valid', null, []);
        }
    }

    //get all city filtered by province
    public function getCityByProvince(Request $request, $parentCode){
        $cek = User::where('api_token',$request->bearerToken())->first();
        if($cek && $request->bearerToken()){
            //path param
            $parentCode = base64_decode($parentCode);

            if(strlen($parentCode) != 2){
                return apiResponse(false, 400, 'Kode provinsi tidak valid, cek daftar provinsi', null, []);
            }

            $cities = Region::whereRaw("CHAR_LENGTH(code) = 5")->where("code","LIKE","{$parentCode}%")->get();
            foreach($cities as $row){
                $data[] = [
                    'code' => $row->code,
                    'name' => $row->name,
                ];
            }

            if($data){
                return apiResponse(true, 200, 'Data kota ditampilkan', $data, ['total_data' => count($data)]);
            }
            else{
                return apiResponse(true, 200, 'Data kota tidak ditemukan', $data, []);
            }
        }else{
            return apiResponse(false, 401, 'Token tidak valid', null, []);
        }
    }

    //get single city
    public function getCity(Request $request, $code){
        $cek = User::where('api_token',$request->bearerToken())->first();
        if($cek && $request->bearerToken()){
            //path param
            $code = base64_decode($code);
            
            $city = Region::whereRaw("CHAR_LENGTH(code) = 5")->where('code', $code)->first();
            if($city){
                $data = [
                    'code' => $city->code,
                    'name' => $city->name,
                ];
                return apiResponse(true, 200, 'Data kota ditampilkan', $data, []);
            }
            else{
                return apiResponse(true, 200, 'Data kota tidak ditemukan', [], []);
            }
        }
        else{
            return apiResponse(false, 401, 'Token tidak valid', null, []);
        }
    }

    //get all district
    public function getAllDistricts(Request $request){
        $cek = User::where('api_token',$request->bearerToken())->first();
        if($cek && $request->bearerToken()){
            $districts = Region::whereRaw("CHAR_LENGTH(code) = 8")->get();
            foreach($districts as $row){
                $data[] = [
                    'code' => $row->code,
                    'name' => $row->name,
                ];
            }

            if($data){
                return apiResponse(true, 200, 'Data kecamatan ditampilkan', $data, ['total_data' => count($data)]);
            }
            else{
                return apiResponse(true, 200, 'Data kecamatan tidak ditemukan', $data, []);
            }
        }else{
            return apiResponse(false, 401, 'Token tidak valid', null, []);
        }
    }

    //get all district filtered by city
    public function getDistrictByCity(Request $request, $parentCode){
        $cek = User::where('api_token',$request->bearerToken())->first();
        if($cek && $request->bearerToken()){
            //path param
            $parentCode = base64_decode($parentCode);

            if(strlen($parentCode) != 5){
                return apiResponse(false, 400, 'Kode kota tidak valid, cek daftar kota', null, []);
            }

            $districts = Region::whereRaw("CHAR_LENGTH(code) = 8")->where('code','LIKE',"{$parentCode}%")->get();
            foreach($districts as $row){
                $data[] = [
                    'code' => $row->code,
                    'name' => $row->name,
                ];
            }
        
            if($data){
                return apiResponse(true, 200, 'Data kecamatan ditampilkan', $data, ['total_data' => count($data)]);
            }
            else{
                return apiResponse(true, 200, 'Data kecamatan tidak ditemukan', $data, []);
            }
        }else{
            return apiResponse(false, 401, 'Token tidak valid', null, []);
        }
    }
    
    //get single district
    public function getDistrict(Request $request, $code){
        $cek = User::where('api_token',$request->bearerToken())->first();
        if($cek && $request->bearerToken()){
            //path param
            $code = base64_decode($code);
            
            $district = Region::whereRaw("CHAR_LENGTH(code) = 8")->where('code', $code)->first();
            if($district){
                $data = [
                    'code' => $district->code,
                    'name' => $district->name,
                ];
                return apiResponse(true, 200, 'Data kecamatan ditampilkan', $data, []);
            }
            else{
                return apiResponse(true, 200, 'Data kecamatan tidak ditemukan', [], []);
            }
        }
        else{
            return apiResponse(false, 401, 'Token tidak valid', null, []);
        }
    }
}
