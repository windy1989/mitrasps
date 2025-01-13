<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MitraCustomer;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MitraCustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(){
        
    }

    public function getAllData(Request $request){
        $cek_token_user = User::where('api_token',$request->bearerToken())->first();

        if($cek_token_user){
            $offset = $request->query('offset', 0);
            $limit  = $request->query('limit', 100); 
            
            $query_data = MitraCustomer::where('mitra_id',$cek_token_user->id)
                        ->offset($offset)->limit($limit)->get();

            if(count($query_data) > 0){
                foreach($query_data as $row){
                    $data[] = [
                        'code'        => $row->code,
                        'name'        => $row->name,
                        'type'        => $row->type,
                        'branch'      => $row->branch,
                        'phone'       => $row->phone,
                        'email'       => $row->email,
                        'address'     => $row->address,
                        'province'    => ["province_code" => $row->province->code, "province_name" => $row->province->name],
                        'city'        => ["city_code" => $row->city->code, "city_name" => $row->city->name],
                        'district'    => ["district_code" => $row->district->code, "district_name" => $row->district->name],
                        'id_card'     => $row->id_card,
                        'pic_name'    => $row->pic_name,
                        'pic_address' => $row->pic_address,
                        'creditlimit' => $row->creditlimit,
                        'top'         => $row->top,
                        'npwp'        => $row->npwp,
                        'created_at'  => $row->created_at,
                        'updated_at'  => $row->updated_at,
                        'deleted_at'  => $row->deleted_at,
                    ];
                }

                $success = true;
                $message = "Data Customer ditampilkan";

                $response = [
                    "success" => $success,
                    "message" => $message,
                    "data"    => $data,
                ];
            
            }else{

            }
                
        }

        return response()->json($response);
    }

    public function getData(Request $request, $code){
        $success = false;
        $message = "";

        $customer = MitraCustomer::where('code', base64_decode($code))->first();
        foreach($customer as $row){
            $data[] = [
                'code'        => $row->code,
                'name'        => $row->name,
                'type'        => $row->type,
                'branch'      => $row->branch,
                'phone'       => $row->phone,
                'email'       => $row->email,
                'address'     => $row->address,
                'province'    => $row->province->code,
                'city'        => $row->city->code,
                'district'    => $row->district->code,
                'id_card'     => $row->id_card,
                'pic_name'    => $row->pic_name,
                'pic_address' => $row->pic_address,
                'creditlimit' => $row->creditlimit,
                'top'         => $row->top,
                'npwp'        => $row->npwp,
                'created_at'  => $row->created_at,
                'updated_at'  => $row->updated_at,
                'deleted_at'  => $row->deleted_at,
            ];
        }
        $success = true;
        $message = "Data Customer ditampilkan";

        $response = [
                    "success" => $success,
                    "message" => $message,
                    "data"    => $data,
        ];

        return response()->json($customer);
    }

    public function store(Request $request){
        $validation = Validator::make($request->all(), [
            'code'             => 'required|unique',
            'name'             => 'required',
            'type'             => 'required',
            'branch'           => 'required',
            'phone'            => 'required',
            'email'            => 'required',
            'address'          => 'required',
            'province'         => 'required',
            'city'             => 'required',
            'district'         => 'required',
            'id_card'          => 'required',
            'pic_name'         => 'required',
            'pic_address'      => 'required',
            'creditlimit'      => 'required',
            'top'              => 'required',
            'npwp'             => 'required',
        ], [
            'code.required'             => 'Kode Customer tidak boleh kosong.',
            'code.unique'               => 'Kode Customer sudah pernah ada.',
            'name.required'             => 'Nama Customer tidak boleh kosong.',
            'type.required'             => 'Type Customer tidak boleh kosong.',
            'branch.required'           => 'Keterangan Cabang tidak boleh kosong.',
            'phone.required'            => 'No. HP Customer tidak boleh kosong.',
            'email.required'            => 'Email Customer tidak boleh kosong.',
            'address.required'          => 'Alamat Customer tidak boleh kosong.',
            'province.required'         => 'Provinsi Customer tidak boleh kosong.',
            'city.required'             => 'Kota Customer tidak boleh kosong.',
            'district.required'         => 'Distrik Customer tidak boleh kosong.',
            'id_card.required'          => 'No. KTP tidak boleh kosong.',
            'pic_name.required'         => 'Nama PIC tidak boleh kosong.',
            'pic_address.required'      => 'Alamat PIC tidak boleh kosong.',
            'creditlimit.required'      => 'Credit Limit tidak boleh kosong.',
            'top.required'              => 'TOP tidak boleh kosong.',
            'npwp.required'             => 'NPWP tidak boleh kosong.',
        ]);

        //block kode sama
        $cek_kode_customer = MitraCustomer::where('code', $request->code)->count();
        if($cek_kode_customer > 0){
            return response()->json("Fail");
        }

        $customer = MitraCustomer::create($request->all);

        return response()->json($customer, 201); // 200 success, 201 created, 202 accepted, 
    }

    public function update(Request $request){
        $customer = MitraCustomer::create($request->all);
        //kalau kode sudah ada

        //kalau kode sudah ada tapi didelete, restore
        
        //kalau kode sudah ada, update data

        return response()->json($customer, 200); // 200 success, 201 created, 202 accepted, 
    }

    public function delete(Request $request){
        $customer = MitraCustomer::create($request->all);

        return response()->json($customer, 200); // 200 success, 201 created, 202 accepted, 
    }
    
}
