<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MitraCustomer;
use App\Models\User;
use App\Models\Region;
use App\Models\MitraCustomerBilling;
use App\Models\MitraCustomerDelivery;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rule;

class MitraCustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(){
        
    }

    //get all mitra customer data 
    public function getDataAll(Request $request){
        $cek = User::where('api_token',$request->bearerToken())->first();

        if($cek && $request->bearerToken()){
            //query param
            $offset = $request->query('offset', 0);
            $limit  = $request->query('limit', 100); 
            
            $query_data = MitraCustomer::where('mitra_id', $cek->id)
                        ->offset($offset)->limit($limit)->get();

            if(count($query_data) > 0){
                foreach($query_data as $row){
                    $delivery_address = MitraCustomerDelivery::where('mitra_customer_id', $row->id)->first();
                    $billing_address  = MitraCustomerBilling::where('mitra_customer_id', $row->id)->first();

                    $data[] = [
                        'code'             => $row->code,
                        'name'             => $row->name,
                        'type'             => $row->type,
                        'branch_code'      => $row->branch_code,
                        'phone'            => $row->phone,
                        'email'            => $row->email,
                        'address'          => $row->address,
                        'province'         => ["province_code" => $row->province->code, "province_name" => $row->province->name],
                        'city'             => ["city_code"     => $row->city->code,     "city_name"     => $row->city->name],
                        'district'         => ["district_code" => $row->district->code, "district_name" => $row->district->name],
                        'id_card'          => $row->id_card,
                        'pic_name'         => $row->pic_name,
                        'pic_address'      => $row->pic_address,
                        'limit_credit'     => $row->limit_credit,
                        'top'              => $row->top,
                        'delivery_address' => $delivery_address==null ? null : ([
                            "address"  => $delivery_address->address,
                            "province" => $delivery_address->province->name,
                            "city"     => $delivery_address->city->name,
                            "district" => $delivery_address->district->name,
                        ]),
                        'billing_address'  => $delivery_address==null ? null : ([
                            "name"     => $billing_address->name,
                            "notes"    => $billing_address->notes,
                            "npwp"     => $billing_address->npwp,
                            "address"  => $billing_address->address,
                            "province" => $billing_address->province->name,
                            "city"     => $billing_address->city->name,
                            "district" => $billing_address->district->name,
                        ]),
                        'created_at'       => $row->created_at,
                        'updated_at'       => $row->updated_at,
                        'deleted_at'       => $row->deleted_at,
                    ];
                }
                return apiResponse(true, 200, 'Data customer ditampilkan', $data, []);
            }else{
                return apiResponse(true, 200, 'Data customer tidak ditemukan', [], []);
            }
        }else{
            return apiResponse(false, 401, 'Token tidak valid', null, []);
        }
    }

    //get single mitra customer data
    public function getData(Request $request, $code){
        $cek = User::where('api_token',$request->bearerToken())->first();
        if($cek && $request->bearerToken()){
            //path param
            $code = base64_decode($code);

            $customer = MitraCustomer::where('mitra_id', $cek->id)->where('code', $code)->first();
            if($customer){
                $delivery_address = MitraCustomerDelivery::where('mitra_customer_id', $customer->id)->first();
                $billing_address  = MitraCustomerBilling::where('mitra_customer_id', $customer->id)->first();

                $data = [
                    'code'         => $customer->code,
                    'name'         => $customer->name,
                    'type'         => $customer->type,
                    'branch_code'  => $customer->branch_code,
                    'phone'        => $customer->phone,
                    'email'        => $customer->email,
                    'address'      => $customer->address,
                    'province'     => ["province_code" => $customer->province->code, "province_name" => $customer->province->name],
                    'city'         => ["city_code" => $customer->city->code, "city_name" => $customer->city->name],
                    'district'     => ["district_code" => $customer->district->code, "district_name" => $customer->district->name],
                    'id_card'      => $customer->id_card,
                    'pic_name'     => $customer->pic_name,
                    'pic_address'  => $customer->pic_address,
                    'limit_credit' => $customer->limit_credit,
                    'top'          => $customer->top,
                    'delivery_address' => $delivery_address==null ? null : ([
                            "address"  => $delivery_address->address,
                            "province" => $delivery_address->province->name,
                            "city"     => $delivery_address->city->name,
                            "district" => $delivery_address->district->name,
                        ]),
                    'billing_address'  => $delivery_address==null ? null : ([
                            "name"     => $billing_address->name,
                            "notes"    => $billing_address->notes,
                            "npwp"     => $billing_address->npwp,
                            "address"  => $billing_address->address,
                            "province" => $billing_address->province->name,
                            "city"     => $billing_address->city->name,
                            "district" => $billing_address->district->name,
                        ]),
                    'created_at'   => $customer->created_at,
                    'updated_at'   => $customer->updated_at,
                    'deleted_at'   => $customer->deleted_at,
                ];
            
                return apiResponse(true, 200, 'Data customer ditampilkan', $data, []);
            }else{
                return apiResponse(true, 200, 'Data customer tidak ditemukan', null, []);
            }
        }else{
            return apiResponse(false, 401, 'Token tidak valid', null, []);
        }
    }

    public function create(Request $request){
        $cek = User::where('api_token',$request->bearerToken())->first();
        if($cek && $request->bearerToken()){            
            $validation = Validator::make($request->all(), [
                'code'             => 'required',
                'name'             => 'required',
                'type'             => 'required',
                'branch_code'      => 'required',
                'phone'            => 'required',
                'email'            => 'required',
                'address'          => 'required',
                'province_code'    => 'required',
                'city_code'        => 'required',
                'district_code'    => 'required',
                // 'id_card'         => 'required',
                'pic_name'         => 'required',
                'pic_address'      => 'required',
                'limit_credit'     => 'required',
                'top'              => 'required',
                'delivery_address' => 'required',
                'billing_address'  => 'required',
            ], [
                'code.required' => 'Kode Customer tidak boleh kosong.',
                // 'code.unique'               => 'Kode Customer sudah pernah ada.',
                'name.required'             => 'Nama Customer tidak boleh kosong.',
                'type.required'             => 'Type Customer tidak boleh kosong.',
                'branch.required'           => 'Keterangan Cabang tidak boleh kosong.',
                'phone.required'            => 'No. HP Customer tidak boleh kosong.',
                'email.required'            => 'Email Customer tidak boleh kosong.',
                'address.required'          => 'Alamat Customer tidak boleh kosong.',
                'province_code.required'    => 'province_code tidak boleh kosong.',
                'city_code.required'        => 'city_code tidak boleh kosong.',
                'district_code.required'    => 'district_code tidak boleh kosong.',
                // 'id_card.required'          => 'No. KTP tidak boleh kosong.',
                'pic_name.required'         => 'Nama PIC tidak boleh kosong.',
                'pic_address.required'      => 'Alamat PIC tidak boleh kosong.',
                'creditlimit.required'      => 'Credit Limit tidak boleh kosong.',
                'top.required'              => 'TOP tidak boleh kosong.',
                'npwp.required'             => 'NPWP tidak boleh kosong.',
                'delivery_address.required' => 'Delivery Address tidak boleh kosong.',
                'billing_address.required'  => 'Billing Address tidak boleh kosong.',
            ]);
    
            if($validation->fails()) {
                return apiResponse(false, 422, "Data yang dikirimkan tidak valid.", $validation->errors(), []);
            } else {
                DB::beginTransaction();
                try {
                    //cek kode provinsi
                    $errorMessage = [];
                    $errorMessage = $this->cek_kode_area([], $request->province_code, $request->city_code, $request->district_code);
                    if(count($errorMessage) > 0) { return apiResponse(false, 422, "Kode area tidak valid", $errorMessage, []); }

                    $cek_kode_customer = MitraCustomer::where('mitra_id', $cek->id)->where('code', $request->code)->first();
                    if($cek_kode_customer){
                        return apiResponse(false, 422, "Customer sudah ada.", null, []);
                    }

                    $query = MitraCustomer::create([
                        'code'            => $request->code,
                        'name'            => $request->name,
                        'type'            => $request->type,
                        'branch_code'     => $request->branch_code,
                        'phone'           => $request->phone,
                        'email'           => $request->email,
                        'address'         => $request->address,
                        'province_id'     => Region::where('code', $request->province_code)->first()->id,
                        'city_id'         => Region::where('code', $request->city_code)->first()->id,
                        'district_id'     => Region::where('code', $request->district_code)->first()->id,
                        'id_card'         => $request->id_card,
                        'pic_name'        => $request->pic_name,
                        'pic_address'     => $request->pic_address,
                        'limit_credit'    => $request->limit_credit,
                        'top'             => $request->top,
                        'status_approval' => 0,
                        'mitra_id'        => $cek->id,
                        'status'          => 0,
                    ]);
                    
                    foreach($request->delivery_address as $row){
                        $errorMessage = $this->cek_kode_area([], $row['province_code'], $row['city_code'], $row['district_code']);
                        if(count($errorMessage) > 0) { return apiResponse(false, 422, "Kode area pengiriman tidak valid", $errorMessage, []); }

                        MitraCustomerDelivery::create([
                            'mitra_customer_id' => $query->id,
                            'address'           => $row['address'],
                            'country_id'        => 103,
                            'province_id'       => Region::where('code', $row['province_code'])->first()->id,
                            'city_id'           => Region::where('code', $row['city_code'])->first()->id,
                            'district_id'       => Region::where('code', $row['district_code'])->first()->id,
                        ]);
                    }

                    foreach($request->billing_address as $row){
                        $errorMessage = $this->cek_kode_area([], $row['province_code'], $row['city_code'], $row['district_code']);
                        if(count($errorMessage) > 0) { return apiResponse(false, 422, "Kode area penagihan tidak valid", $errorMessage, []); }

                        MitraCustomerBilling::create([
                            'mitra_customer_id' => $query->id,
                            'name'              => $row['name'],
                            'notes'             => $row['notes'],
                            'npwp'              => $row['npwp'],
                            'address'           => $row['address'],
                            'country_id'        => 103,
                            'province_id'       => Region::where('code', $row['province_code'])->first()->id,
                            'city_id'           => Region::where('code', $row['city_code'])->first()->id,
                            'district_id'       => Region::where('code', $row['district_code'])->first()->id,
                        ]);
                    }

                    DB::commit();
                    return apiResponse(true, 201, "Data customer berhasil disimpan", null, []);
                }
                catch(\Exception $e){
                    DB::rollback();
                    $message = $e->getMessage();
                    info($message);
                    return apiResponse(false, 401, "Data gagal disimpan" , $message, []);
                }
            }
        }else{
            return apiResponse(false, 401, 'Token tidak valid', null, []);
        }
    }

    public function update(Request $request, $code){
        $cek = User::where('api_token',$request->bearerToken())->first();
        if($cek && $request->bearerToken()){
            //path param
            $code = base64_decode($code);

            $validation = Validator::make($request->all(), [
                'code'             => 'required',
                'name'             => 'required',
                'type'             => 'required',
                'branch_code'      => 'required',
                'phone'            => 'required',
                'email'            => 'required',
                'address'          => 'required',
                'province_code'    => 'required',
                'city_code'        => 'required',
                'district_code'    => 'required',
                // 'id_card'         => 'required',
                'pic_name'         => 'required',
                'pic_address'      => 'required',
                'limit_credit'     => 'required',
                'top'              => 'required',
                'delivery_address' => 'required',
                'billing_address'  => 'required',
            ], [
                'code.required' => 'Kode Customer tidak boleh kosong.',
                // 'code.unique'               => 'Kode Customer sudah pernah ada.',
                'name.required'             => 'Nama Customer tidak boleh kosong.',
                'type.required'             => 'Type Customer tidak boleh kosong.',
                'branch.required'           => 'Keterangan Cabang tidak boleh kosong.',
                'phone.required'            => 'No. HP Customer tidak boleh kosong.',
                'email.required'            => 'Email Customer tidak boleh kosong.',
                'address.required'          => 'Alamat Customer tidak boleh kosong.',
                'province_code.required'    => 'province_code tidak boleh kosong.',
                'city_code.required'        => 'city_code tidak boleh kosong.',
                'district_code.required'    => 'district_code tidak boleh kosong.',
                // 'id_card.required'          => 'No. KTP tidak boleh kosong.',
                'pic_name.required'         => 'Nama PIC tidak boleh kosong.',
                'pic_address.required'      => 'Alamat PIC tidak boleh kosong.',
                'creditlimit.required'      => 'Credit Limit tidak boleh kosong.',
                'top.required'              => 'TOP tidak boleh kosong.',
                'npwp.required'             => 'NPWP tidak boleh kosong.',
                'delivery_address.required' => 'Delivery Address tidak boleh kosong.',
                'billing_address.required'  => 'Billing Address tidak boleh kosong.',
            ]);

            if($validation->fails()) {
                return apiResponse(false, 422, "Data yang dikirimkan tidak valid.", $validation->errors(), []);
            } else {
                DB::beginTransaction();
                try {
                    $customer = MitraCustomer::where('mitra_id', $cek->id)->where('code', $code)->where('status', '1')->first();
                    //kalau kode sudah ada
                    if($customer){
                        //kalau kode sudah ada tapi didelete, restore
                        //kalau kode sudah ada, update data
                        
                        $customer->code            = $request->code;
                        $customer->name            = $request->name;
                        $customer->type            = $request->type;
                        $customer->branch_code     = $request->branch_code;
                        $customer->phone           = $request->phone;
                        $customer->email           = $request->email;
                        $customer->address         = $request->address;
                        $customer->province_id     = Region::where('code', $request->province_code)->first()->id;
                        $customer->city_id         = Region::where('code', $request->city_code)->first()->id;
                        $customer->district_id     = Region::where('code', $request->district_code)->first()->id;
                        $customer->id_card         = $request->id_card;
                        $customer->pic_name        = $request->pic_name;
                        $customer->pic_address     = $request->pic_address;
                        $customer->limit_credit    = $request->limit_credit;
                        $customer->top             = $request->top;
                        $customer->top_internal    = $request->top;
                        $customer->status_approval = 0;
                        $customer->mitra_id        = $cek->id;
                        $customer->status          = 0;
                        $customer->save();
                        
                        return apiResponse(false, 422, "Data Customer berhassil diupdate.", null, []);
                    }else{
                        return apiResponse(false, 422, "Data Customer tidak ditemukan.", null, []);
                    }
                }
                catch(\Exception $e){
                    DB::rollback();
                    $message = $e->getMessage();
                    info($message);
                    return apiResponse(false, 401, "Data gagal disimpan" , $message, []);
                }
            }
        }else{
            return apiResponse(false, 401, 'Token tidak valid', null, []);
        }
    }

    public function delete(Request $request){
        $cek = User::where('api_token',$request->bearerToken())->first();
        if($cek && $request->bearerToken()){
            $customer = MitraCustomer::create($request->all);
    
            return response()->json($customer, 200); // 200 success, 201 created, 202 accepted
        }else{
            return apiResponse(false, 401, 'Token tidak valid', null, []);
        }
    }

    private function cek_kode_area($errorMessage, $province_code, $city_code, $district_code){
        if(strlen($province_code) != 2){ $errorMessage[] = 'Field province_code tidak valid'; }
        if(strlen($city_code) != 5){ $errorMessage[] = 'Field city_code tidak valid'; }
        if(strlen($district_code) != 8){ $errorMessage[] = 'Field district_code tidak valid'; }
        // if(count($errorMessage) > 0) { return apiResponse(false, 422, "Kode area tidak sesuai format", $errorMessage, []); }
        
        // $errorMessage = [];
        $province_cek = Region::whereRaw("CHAR_LENGTH(code) = 2")->where('code', $province_code)->first();
        if(!$province_cek){ $errorMessage[] = 'Field province_code tidak ditemukan'; }
        $city_cek = Region::whereRaw("CHAR_LENGTH(code) = 5")->where('code', 'LIKE', "{$province_code}%")->where('code', $city_code)->first();
        if(!$city_cek){ $errorMessage[] = 'Field city_code tidak ditemukan / tidak cocok dengan parent province_code'; }
        $district_cek = Region::whereRaw("CHAR_LENGTH(code) = 8")->where('code', 'LIKE', "{$city_code}%")->where('code', $district_code)->first();
        if(!$district_cek){ $errorMessage[] = 'Field district_code tidak ditemukan / tidak cocok dengan parent city_code'; }

        return $errorMessage;
    }
    
}
