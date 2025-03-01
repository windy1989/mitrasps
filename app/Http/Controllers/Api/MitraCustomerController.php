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
                        'province_code'    => $row->province->code,
                        'province_name'    => $row->province->name,
                        'city_code'        => $row->city->code,
                        'city_name'        => $row->city->name,
                        'district_code'    => $row->district->code,
                        'district_name'    => $row->district->name,
                        'id_card'          => $row->id_card,
                        'pic_name'         => $row->pic_name,
                        'pic_address'      => $row->pic_address,
                        'limit_credit'     => $row->limit_credit,
                        'top'              => $row->top,
                        'payment_account'  => $row->payment_account,
                        'status_approval'  => $row->statusApprovalRaw(),
                        'delivery_address' => $delivery_address==null ? null : ([
                            'address'       => $delivery_address->address,
                            'province_code' => $delivery_address->province->code,
                            'province_name' => $delivery_address->province->name,
                            'city_code'     => $delivery_address->city->code,
                            'city_name'     => $delivery_address->city->name,
                            'district_code' => $delivery_address->district->code,
                            'district_name' => $delivery_address->district->name,
                        ]),
                        'billing_address'  => $delivery_address==null ? null : ([
                            'name'          => $billing_address->name,
                            'notes'         => $billing_address->notes,
                            'npwp'          => $billing_address->npwp,
                            'address'       => $billing_address->address,
                            'province_code' => $billing_address->province->code,
                            'province_name' => $billing_address->province->name,
                            'city_code'     => $billing_address->city->code,
                            'city_name'     => $billing_address->city->name,
                            'district_code' => $billing_address->district->code,
                            'district_name' => $billing_address->district->name,
                        ]),
                        'created_at'       => $row->created_at,
                        'updated_at'       => $row->updated_at,
                        'deleted_at'       => $row->deleted_at,
                    ];
                }
                return apiResponse(true, 200, 'Data customer ditampilkan', $data, []);
            }else{
                return apiResponse(false, 404, 'Data customer tidak ditemukan', [], []);
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
                    'code'          => $customer->code,
                    'name'             => $customer->name,
                    'type'             => $customer->type,
                    'branch_code'      => $customer->branch_code,
                    'phone'            => $customer->phone,
                    'email'            => $customer->email,
                    'address'          => $customer->address,
                    'province_code'    => $customer->province->code,
                    'province_name'    => $customer->province->name,
                    'city_code'        => $customer->city->code,
                    'city_name'        => $customer->city->name,
                    'district_code'    => $customer->district->code,
                    'district_name'    => $customer->district->name,
                    'id_card'          => $customer->id_card,
                    'pic_name'         => $customer->pic_name,
                    'pic_address'      => $customer->pic_address,
                    'limit_credit'     => $customer->limit_credit,
                    'top'              => $customer->top,
                    'payment_account'  => $customer->payment_account,
                    'status_approval'  => $customer->statusApprovalRaw(),
                    'delivery_address' => $delivery_address==null ? null : ([
                            'address'       => $delivery_address->address,
                            'province_code' => $delivery_address->province->code,
                            'province_name' => $delivery_address->province->name,
                            'city_code'     => $delivery_address->city->code,
                            'city_name'     => $delivery_address->city->name,
                            'district_code' => $delivery_address->district->code,
                            'district_name' => $delivery_address->district->name,
                        ]),
                    'billing_address'  => $delivery_address==null ? null : ([
                            'name'          => $billing_address->name,
                            'notes'         => $billing_address->notes,
                            'npwp'          => $billing_address->npwp,
                            'address'       => $billing_address->address,
                            'province_code' => $billing_address->province->code,
                            'province_name' => $billing_address->province->name,
                            'city_code'     => $billing_address->city->code,
                            'city_name'     => $billing_address->city->name,
                            'district_code' => $billing_address->district->code,
                            'district_name' => $billing_address->district->name,
                        ]),
                    'created_at'      => $customer->created_at,
                    'updated_at'      => $customer->updated_at,
                    'deleted_at'      => $customer->deleted_at,
                ];
            
                return apiResponse(true, 200, 'Data customer ditampilkan', $data, []);
            }else{
                return apiResponse(false, 404, 'Data customer tidak ditemukan', null, []);
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
                // 'email'            => 'required',
                'address'          => 'required',
                'province_code'    => 'required',
                'city_code'        => 'required',
                'district_code'    => 'required',
                'id_card'          => 'required',
                'pic_name'         => 'required',
                'pic_address'      => 'required',
                'limit_credit'     => 'required',
                'top'              => 'required',
                'payment_account'  => 'required',
                'delivery_address' => 'required',
                'billing_address'  => 'required',
            ], [
                'code.required'             => 'Kode Customer tidak boleh kosong.',
                // 'code.unique'               => 'Kode Customer sudah pernah ada.',
                'name.required'             => 'Nama Customer tidak boleh kosong.',
                'type.required'             => 'Type Customer tidak boleh kosong.',
                'branch.required'           => 'Keterangan Cabang tidak boleh kosong.',
                'phone.required'            => 'No. HP Customer tidak boleh kosong.',
                // 'email.required'            => 'Email Customer tidak boleh kosong.',
                'address.required'          => 'Alamat Customer tidak boleh kosong.',
                'province_code.required'    => 'province_code tidak boleh kosong.',
                'city_code.required'        => 'city_code tidak boleh kosong.',
                'district_code.required'    => 'district_code tidak boleh kosong.',
                'id_card.required'          => 'No. KTP tidak boleh kosong.',
                'pic_name.required'         => 'Nama PIC tidak boleh kosong.',
                'pic_address.required'      => 'Alamat PIC tidak boleh kosong.',
                'creditlimit.required'      => 'Credit Limit tidak boleh kosong.',
                'top.required'              => 'TOP tidak boleh kosong.',
                'payment_account.required'  => 'Rekening Pembayaran tidak boleh kosong.',
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
                    $errorMessage = $this->cek_kode_area($errorMessage, $request->province_code, $request->city_code, $request->district_code);
                    if(count($errorMessage) > 0) { return apiResponse(false, 422, "Kode area tidak valid", $errorMessage, []); }

                    $cek_kode_customer = MitraCustomer::where('mitra_id', $cek->id)->where('code', $request->code)->first();
                    if($cek_kode_customer){
                        return apiResponse(false, 422, "Kode Customer sudah ada.", null, []);
                    }

                    //Area JABODETABEKSER 56 hari -> internal 66.
                    //Area lain seluruh Jawa 42 hari -> internal 48 hari.
                    // $top_internal = $request->top;
                    // if($request->top == 56){ $top_internal = 66; }
                    // if($request->top == 42){ $top_internal = 48; }

                    $query = MitraCustomer::create([
                        'code'            => strtoupper($request->code),
                        'name'            => strtoupper($request->name),
                        'type'            => strtoupper($request->type),
                        'branch_code'     => strtoupper($request->branch_code),
                        'phone'           => $request->phone,
                        'email'           => strtoupper($request->email),
                        'address'         => strtoupper($request->address),
                        'province_id'     => Region::where('code', $request->province_code)->first()->id,
                        'city_id'         => Region::where('code', $request->city_code)->first()->id,
                        'district_id'     => Region::where('code', $request->district_code)->first()->id,
                        'id_card'         => strtoupper($request->id_card),
                        'pic_name'        => strtoupper($request->pic_name),
                        'pic_address'     => strtoupper($request->pic_address),
                        'limit_credit'    => $request->limit_credit,
                        'top'             => $request->top,
                        'top_internal'    => 90, //$top_internal,
                        'payment_account' => $request->payment_account,
                        'status_approval' => 2,
                        'mitra_id'        => $cek->id,
                        'status'          => 2,
                    ]);
                    
                    foreach($request->delivery_address as $row){
                        if(!$row['address']) { $errorMessage[] = "Address untuk dokumen penagihan harus diisi"; }
                        $errorMessage = $this->cek_kode_area($errorMessage, $row['province_code'], $row['city_code'], $row['district_code']);
                        if(count($errorMessage) > 0) { return apiResponse(false, 422, "delivery_address tidak valid", $errorMessage, []); }

                        MitraCustomerDelivery::create([
                            'mitra_customer_id' => $query->id,
                            'address'           => strtoupper($row['address']),
                            'country_id'        => 103,
                            'province_id'       => Region::where('code', $row['province_code'])->first()->id,
                            'city_id'           => Region::where('code', $row['city_code'])->first()->id,
                            'district_id'       => Region::where('code', $row['district_code'])->first()->id,
                        ]);
                    }

                    foreach($request->billing_address as $row){
                        if(!$row['name']) { $errorMessage[] = "Nama untuk dokumen penagihan harus diisi"; }
                        if(!$row['address']) { $errorMessage[] = "Address untuk dokumen penagihan harus diisi"; }
                        $errorMessage = $this->cek_kode_area($errorMessage, $row['province_code'], $row['city_code'], $row['district_code']);
                        if(count($errorMessage) > 0) { return apiResponse(false, 422, "billing_address tidak valid", $errorMessage, []); }
                        
                        MitraCustomerBilling::create([
                            'mitra_customer_id' => $query->id,
                            'name'              => strtoupper($row['name']),
                            'notes'             => strtoupper($row['notes']),
                            'npwp'              => $row['npwp'],
                            'address'           => strtoupper($row['address']),
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
                    return apiResponse(false, 400, "Data gagal disimpan" , $message, []);
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
                // 'email'            => 'required',
                'address'          => 'required',
                'province_code'    => 'required',
                'city_code'        => 'required',
                'district_code'    => 'required',
                'id_card'         => 'required',
                'pic_name'         => 'required',
                'pic_address'      => 'required',
                'limit_credit'     => 'required',
                'top'              => 'required',
                'payment_account'  => 'required',
                'delivery_address' => 'required',
                'billing_address'  => 'required',
            ], [
                'code.required'             => 'Kode Customer tidak boleh kosong.',
                // 'code.unique'               => 'Kode Customer sudah pernah ada.',
                'name.required'             => 'Nama Customer tidak boleh kosong.',
                'type.required'             => 'Type Customer tidak boleh kosong.',
                'branch.required'           => 'Keterangan Cabang tidak boleh kosong.',
                'phone.required'            => 'No. HP Customer tidak boleh kosong.',
                // 'email.required'            => 'Email Customer tidak boleh kosong.',
                'address.required'          => 'Alamat Customer tidak boleh kosong.',
                'province_code.required'    => 'province_code tidak boleh kosong.',
                'city_code.required'        => 'city_code tidak boleh kosong.',
                'district_code.required'    => 'district_code tidak boleh kosong.',
                'id_card.required'          => 'No. KTP tidak boleh kosong.',
                'pic_name.required'         => 'Nama PIC tidak boleh kosong.',
                'pic_address.required'      => 'Alamat PIC tidak boleh kosong.',
                'creditlimit.required'      => 'Credit Limit tidak boleh kosong.',
                'top.required'              => 'TOP tidak boleh kosong.',
                'payment_account.required'  => 'Rekening Pembayaran tidak boleh kosong.',
                'npwp.required'             => 'NPWP tidak boleh kosong.',
                'delivery_address.required' => 'Delivery Address tidak boleh kosong.',
                'billing_address.required'  => 'Billing Address tidak boleh kosong.',
            ]);

            if($validation->fails()) {
                return apiResponse(false, 422, "Data yang dikirimkan tidak valid.", $validation->errors(), []);
            } else {
                DB::beginTransaction();
                try {
                    $customer = MitraCustomer::where('mitra_id', $cek->id)->where('code', $code)->whereIn('status', array('1','2'))->first(); // 1 aktif, 2 non aktif
                    //kalau kode sudah ada
                    if($customer){
                        $errorMessage = [];
                        if ($customer->status_approval == 2 || $customer->status_approval == 3){
                            return apiResponse(false, 422, "Status Customer sedang ".$customer->statusApprovalRaw()." dan belum bisa diupdate lagi", $errorMessage, []); 
                        }
                        $errorMessage = $this->cek_kode_area([], $request->province_code, $request->city_code, $request->district_code);
                        if(count($errorMessage) > 0) { return apiResponse(false, 422, "Kode area tidak valid", $errorMessage, []); }
                        //kalau kode sudah ada tapi didelete, restore
                        //kalau kode sudah ada, update data
                        
                        //Area JABODETABEKSER 56 hari -> internal 66.
                        //Area lain seluruh Jawa 42 hari -> internal 48 hari.
                        // $top_internal = $request->top;
                        // if($request->top == 56){ $top_internal = 66; }
                        // if($request->top == 42){ $top_internal = 48; }

                        $customer->code            = strtoupper($request->code);
                        $customer->name            = strtoupper($request->name);
                        $customer->type            = strtoupper($request->type);
                        $customer->branch_code     = strtoupper($request->branch_code);
                        $customer->phone           = $request->phone;
                        $customer->email           = strtoupper($request->email);
                        $customer->address         = strtoupper($request->address);
                        $customer->province_id     = Region::where('code', $request->province_code)->first()->id;
                        $customer->city_id         = Region::where('code', $request->city_code)->first()->id;
                        $customer->district_id     = Region::where('code', $request->district_code)->first()->id;
                        $customer->id_card         = strtoupper($request->id_card);
                        $customer->pic_name        = strtoupper($request->pic_name);
                        $customer->pic_address     = strtoupper($request->pic_address);
                        $customer->limit_credit    = $request->limit_credit;
                        $customer->payment_account = $request->payment_account;
                        $customer->status_approval = 3;
                        $customer->top             = $request->top;
                        // $customer->top_internal    = $top_internal;
                        $customer->save();

                        $customer->deliveryAddress()->delete();
                        $customer->billingAddress()->delete();

                        foreach($request->delivery_address as $row){
                            if(!$row['address']) { $errorMessage[] = "Address untuk dokumen penagihan harus diisi"; }
                            $errorMessage = $this->cek_kode_area($errorMessage, $row['province_code'], $row['city_code'], $row['district_code']);
                            if(count($errorMessage) > 0) { return apiResponse(false, 422, "Data alamat pengiriman tidak valid", $errorMessage, []); }
    
                            MitraCustomerDelivery::create([
                                'mitra_customer_id' => $customer->id,
                                'address'           => strtoupper($row['address']),
                                'country_id'        => 103,
                                'province_id'       => Region::where('code', $row['province_code'])->first()->id,
                                'city_id'           => Region::where('code', $row['city_code'])->first()->id,
                                'district_id'       => Region::where('code', $row['district_code'])->first()->id,
                            ]);
                        }
    
                        foreach($request->billing_address as $row){
                            if(!$row['name']) { $errorMessage[] = "Nama untuk dokumen penagihan harus diisi"; }
                            if(!$row['address']) { $errorMessage[] = "Address untuk dokumen penagihan harus diisi"; }
                            $errorMessage = $this->cek_kode_area($errorMessage, $row['province_code'], $row['city_code'], $row['district_code']);
                            if(count($errorMessage) > 0) { return apiResponse(false, 422, "Data alamat & dokumen penagihan tidak valid", $errorMessage, []); }
    
                            MitraCustomerBilling::create([
                                'mitra_customer_id' => $customer->id,
                                'name'              => strtoupper($row['name']),
                                'notes'             => strtoupper($row['notes']),
                                'npwp'              => $row['npwp'],
                                'address'           => strtoupper($row['address']),
                                'country_id'        => 103,
                                'province_id'       => Region::where('code', $row['province_code'])->first()->id,
                                'city_id'           => Region::where('code', $row['city_code'])->first()->id,
                                'district_id'       => Region::where('code', $row['district_code'])->first()->id,
                            ]);
                        }
                        
                        DB::commit();
                        return apiResponse(true, 200, "Data customer berhasil diupdate.", null, []);
                    }else{
                        return apiResponse(false, 422, "Data customer tidak berhasil diupdate.", null, []);
                    }
                }
                catch(\Exception $e){
                    DB::rollback();
                    $message = $e->getMessage();
                    info($message);
                    return apiResponse(false, 422, "Data gagal disimpan" , $message, []);
                }
            }
        }else{
            return apiResponse(false, 401, 'Token tidak valid', null, []);
        }
    }

    public function delete(Request $request, $code){
        $cek = User::where('api_token',$request->bearerToken())->first();
        if($cek && $request->bearerToken()){
            $code = base64_decode($code);
            $customer = MitraCustomer::where('mitra_id', $cek->id)->where('code', $code)->where('status', '1')->first();

            if($customer){
                $customer->update(['status' => 2]);
                $customer->delete();
                return apiResponse(true, 200, "Data customer berhasil dihapus.", null, []);
            }else{
                return apiResponse(false, 422, "Data customer tidak ditemukan.", null, []);
            }
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
