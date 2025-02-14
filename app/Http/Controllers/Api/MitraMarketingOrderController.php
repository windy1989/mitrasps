<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Menu;
use App\Models\MitraCustomer;
use App\Models\MitraMarketingOrderDetail;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MitraMarketingOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MitraMarketingOrderController extends Controller
{
    public function create(Request $request) {
        $cek = User::where('api_token',$request->bearerToken())->first();
        if($cek && $request->bearerToken()){
            $validation = Validator::make($request->all(), [
                'document_no'            => 'required',
                'customer_code'          => 'required',
                'branch_code'            => 'required',
                'post_date'              => 'required',
                'valid_date'             => 'required',
                'type_delivery'          => 'required',
                'delivery_date'          => 'required',
                'delivery_address'       => 'required',
                'delivery_province_code' => 'required',
                'delivery_city_code'     => 'required',
                'delivery_district_code' => 'required',
                'payment_type'           => 'required',
                'percent_dp'             => $request->payment_type == '1' ? 'required' : '',
                'details'                => 'required|array',
            ], [
                'document_no.required'            => 'Kode dokumen penghubung tidak boleh kosong.',
                'customer_code.required'          => 'Kode Customer tidak boleh kosong.',
                'branch_code.required'            => 'Kode Branch tidak boleh kosong.',
                'post_date.required'              => 'Tanggal post tidak boleh kosong.',
                'valid_date.required'             => 'Tanggal valid SO tidak boleh kosong.',
                'type_delivery.required'          => 'Tipe pengiriman tidak boleh kosong. 1 : loco, 2 : franco.',
                'delivery_date.required'          => 'Tanggal permintaan pengiriman tidak boleh kosong.',
                'delivery_address.required'       => 'Alamat pengiriman tidak boleh kosong.',
                'delivery_province_code.required' => 'Provinsi pengiriman tidak boleh kosong.',
                'delivery_city_code.required'     => 'Kota/Kabupaten pengiriman tidak boleh kosong.',
                'delivery_district_code.required' => 'Kecamatan pengiriman tidak boleh kosong.',
                'payment_type.required'           => 'Tipe pembayaran tidak boleh kosong. 1 : DP, 2 : Kredit.',
                'percent_dp.required'             => 'Prosentase DP tidak boleh kosong.',
                'details.required'                => 'Detail item tidak boleh kosong.',
                'details.array'                   => 'Detail harus dalam bentuk array.',
            ]);

            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            } else {

                DB::beginTransaction();
                try {
                    $menu = Menu::where('url', 'mitra_marketing_order')->first();
                    $newCode = MitraMarketingOrder::generateCode($menu->document_code.date('y',strtotime($request->post_date)).'00');
                    
                    $errorMessage = [];
                    /* $customer = User::whereHas('mitraCustomer',function($query)use($request){
                        $query->where('code',$request->customer_code);
                    })->where('status','1')->where('type','2')->first(); */
                    $customer = MitraCustomer::where('code',$request->customer_code)->whereHas('user',function($query){
                        $query->where('status','1')->where('type','2');
                    })->where('status_approval','1')->where('status','1')->whereNotNull('user_id')->first();
                    if(!$customer){
                        $errorMessage[] = 'Customer tidak ditemukan atau belum diapprove oleh marketing.';
                    }

                    if(!in_array($request->type_delivery,['1','2'])){
                        $errorMessage[] = 'Tipe Pengiriman harap diisikan "1" : Loco / "2" : Franco';
                    }

                    if(!in_array($request->payment_type,['1','2'])){
                        $errorMessage[] = 'Status pembelian harap diisikan "1" : DP / "2" : Kredit.';
                    }

                    if($customer){
                        foreach($request->details as $key => $row){
                            $item = null;
                            $item = Item::where('code',$row['item_code'])->where('brand_id',$customer->brand_id)->where('status','1')->first();
                            if(!$item){
                                $errorMessage[] = 'Item dengan kode '.$row['item_code'].' tidak ditemukan pada broker / user terpilih.';
                            }
                        }
                    }

                    if(count($errorMessage) == 0){
                        $querycheck = MitraMarketingOrder::where('user_id',$cek->id)->where('document_no',$request->document_no)->whereIn('status',['1','2','3'])->first();

                        if(!$querycheck){
                            $query = MitraMarketingOrder::create([
                                'code'                   => $newCode,
                                'user_id'                => $cek->id,
                                'account_id'             => $customer->user_id,
                                'post_date'              => $request->post_date,
                                'valid_date'             => $request->valid_date,
                                'document_no'            => strtoupper($request->document_no),
                                'branch_code'            => strtoupper($request->branch_code),
                                'type_delivery'          => $request->type_delivery,
                                'delivery_date'          => $request->delivery_date,
                                'delivery_address'       => strtoupper($request->delivery_address),
                                'delivery_province_code' => $request->delivery_province_code,
                                'delivery_city_code'     => $request->delivery_city_code,
                                'delivery_district_code' => $request->delivery_district_code,
                                'payment_type'           => $request->payment_type,
                                'percent_dp'             => $request->percent_dp ?? NULL,
                                'note'                   => strtoupper($request->note),
                                'status'                 => '1',
                            ]);
    
                            $total = 0;
                            $tax = 0;
                            $grandtotal = 0;
                            foreach($request->details as $key => $row){
                                $item = Item::where('code',$row['item_code'])->where('status','1')->first();
                                $pricetax = round((11 / 100) * $row['price'],2);
                                $final_price = $row['price'] + $pricetax;
                                $qty = round($row['qty'] * $item->sellConversion(),3);
                                $rowtotal = round($qty * $row['price'],2);
                                $rowtax = round((11 / 100) * $rowtotal,2);
                                $rowgrandtotal = round($rowtotal + $rowtax,2);
                                MitraMarketingOrderDetail::create([
                                    'mitra_marketing_order_id' => $query->id,
                                    'item_id'                  => $item->id,
                                    'qty'                      => $qty,
                                    'price'                    => $row['price'],
                                    'percent_tax'              => 11,
                                    'final_price'              => $final_price,
                                    'total'                    => $rowtotal,
                                    'tax'                      => $rowtax,
                                    'grandtotal'               => $rowgrandtotal,
                                    'note'                     => strtoupper($row['note']),
                                ]);
                                $total += $rowtotal;
                                $tax += $rowtax;
                            }

                            $tax = floor($tax);
                            $grandtotal = $total + $tax;

                            $query->update([
                                'total'     => $total,
                                'tax'       => $tax,
                                'grandtotal'=> $grandtotal,
                            ]);
                            
                            $response = [
                                'status'    => 200,
                                'message'   => 'Data berhasil disimpan',
                            ];
                        }else{
                            $response = [
                                'status'    => 500,
                                'message'   => 'Nomor dokumen penghubung telah masuk ke dalam sistem. Silahkan cek status dokumen.',
                            ];
                        }
                    }else{
                        $response = [
                            'status'    => 424,
                            'error'     => $errorMessage
                        ];
                    }

                    DB::commit();
                }catch(\Exception $e){
                    $message = $e->getMessage();
                    info($message);
                    DB::rollback();
                    $response = [
                        'status'    => 500,
                        'message'   => $message
                    ];
                }
            }
        }else{
            $response = [
                'status'    => 401,
                'message'   => 'Token tidak ditemukan'
            ];
        }

        return response()->json($response);
    }

    public function update(Request $request) {
        $cek = User::where('api_token',$request->bearerToken())->first();
        if($cek && $request->bearerToken()){
            $validation = Validator::make($request->all(), [
                'document_no'            => 'required',
                'customer_code'          => 'required',
                'branch_code'            => 'required',
                'post_date'              => 'required',
                'valid_date'             => 'required',
                'type_delivery'          => 'required',
                'delivery_date'          => 'required',
                'delivery_address'       => 'required',
                'delivery_province_code' => 'required',
                'delivery_city_code'     => 'required',
                'delivery_district_code' => 'required',
                'payment_type'           => 'required',
                'percent_dp'             => $request->payment_type == '1' ? 'required' : '',
                'details'                => 'required|array',
            ], [
                'document_no.required'            => 'Kode dokumen penghubung tidak boleh kosong.',
                'customer_code.required'          => 'Kode Customer tidak boleh kosong.',
                'branch_code.required'            => 'Kode Branch tidak boleh kosong.',
                'post_date.required'              => 'Tanggal post tidak boleh kosong.',
                'valid_date.required'             => 'Tanggal valid SO tidak boleh kosong.',
                'type_delivery.required'          => 'Tipe pengiriman tidak boleh kosong. 1 : loco, 2 : franco.',
                'delivery_date.required'          => 'Tanggal permintaan pengiriman tidak boleh kosong.',
                'delivery_address.required'       => 'Alamat pengiriman tidak boleh kosong.',
                'delivery_province_code.required' => 'Provinsi pengiriman tidak boleh kosong.',
                'delivery_city_code.required'     => 'Kota/Kabupaten pengiriman tidak boleh kosong.',
                'delivery_district_code.required' => 'Kecamatan pengiriman tidak boleh kosong.',
                'payment_type.required'           => 'Tipe pembayaran tidak boleh kosong. 1 : DP, 2 : Kredit.',
                'percent_dp.required'             => 'Prosentase DP tidak boleh kosong.',
                'details.required'                => 'Detail item tidak boleh kosong.',
                'details.array'                   => 'Detail harus dalam bentuk array.',
            ]);

            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            } else {

                DB::beginTransaction();
                try {
                    $query = MitraMarketingOrder::where('document_no',$request->document_no)->where('status','1')->where('user_id',$cek->id)->first();
                    
                    if($query){
                        $errorMessage = [];
                        $customer = User::whereHas('mitraCustomer',function($query)use($request){
                            $query->where('code',$request->customer_code);
                        })->where('status','1')->where('type','2')->first();
                        if(!$customer){
                            $errorMessage[] = 'Customer tidak ditemukan atau belum diapprove oleh marketing.';
                        }

                        if(!in_array($request->type_delivery,['1','2'])){
                            $errorMessage[] = 'Tipe Pengiriman harap diisikan "1" : Loco / "2" : Franco';
                        }

                        if(!in_array($request->payment_type,['1','2'])){
                            $errorMessage[] = 'Status pembelian harap diisikan "1" : DP / "2" : Kredit.';
                        }

                        if($customer){
                            foreach($request->details as $key => $row){
                                $item = null;
                                $item = Item::where('code',$row['item_code'])->where('brand_id',$customer->brand_id)->where('status','1')->first();
                                if(!$item){
                                    $errorMessage[] = 'Item dengan kode '.$row['item_code'].' tidak ditemukan.';
                                }
                            }
                        }

                        if(count($errorMessage) == 0){
                            $query->user_id                = $cek->id;
                            $query->account_id             = $customer->id;
                            $query->post_date              = $request->post_date;
                            $query->valid_date             = $request->valid_date;
                            $query->document_no            = strtoupper($request->document_no);
                            $query->branch_code            = strtoupper($request->branch_code);
                            $query->type_delivery          = $request->type_delivery;
                            $query->delivery_date          = $request->delivery_date;
                            $query->delivery_address       = strtoupper($request->delivery_address);
                            $query->delivery_province_code = $request->delivery_province_code;
                            $query->delivery_city_code     = $request->delivery_city_code;
                            $query->delivery_district_code = $request->delivery_district_code;
                            $query->payment_type           = $request->payment_type;
                            $query->percent_dp             = $request->percent_dp ?? NULL;
                            $query->note                   = strtoupper($request->note);
                            $query->total                  = $request->total;
                            $query->tax                    = $request->tax;
                            $query->grandtotal             = $request->grandtotal;
                            $query->status                 = '1';
                            $query->save();
    
                            $query->mitraMarketingOrderDetail()->delete();

                            $total = 0;
                            $tax = 0;
                            $grandtotal = 0;
                            foreach($request->details as $key => $row){
                                $item = Item::where('code',$row['item_code'])->where('status','1')->first();
                                $pricetax = round(($row['percent_tax'] / 100) * $row['price'],2);
                                $final_price = $row['price'] + $pricetax;
                                $qty = round($row['qty'] * $item->sellConversion(),3);
                                $rowtotal = round($qty * $row['price'],2);
                                $rowtax = round(($row['percent_tax'] / 100) * $rowtotal,2);
                                $rowgrandtotal = round($rowtotal + $rowtax,2);
                                MitraMarketingOrderDetail::create([
                                    'mitra_marketing_order_id' => $query->id,
                                    'item_id'                  => $item->id,
                                    'qty'                      => $qty,
                                    'price'                    => $row['price'],
                                    'percent_tax'              => $row['percent_tax'],
                                    'final_price'              => $final_price,
                                    'total'                    => $rowtotal,
                                    'tax'                      => $rowtax,
                                    'grandtotal'               => $rowgrandtotal,
                                    'note'                     => strtoupper($row['note']),
                                ]);
                                $total += $rowtotal;
                                $tax += $rowtax;
                            }

                            $tax = floor($tax);
                            $grandtotal = $total + $tax;

                            MitraMarketingOrder::find($query->id)->update([
                                'total'     => $total,
                                'tax'       => $tax,
                                'grandtotal'=> $grandtotal,
                            ]);
                            
                            $response = [
                                'status'    => 200,
                                'message'   => 'Data berhasil disimpan',
                            ];
                        }else{
                            $response = [
                                'status'    => 424,
                                'error'     => $errorMessage
                            ];
                        }

                        DB::commit();
                    }else{
                        $response = [
                            'status'    => 500,
                            'message'   => 'Data tidak ditemukan.',
                        ];
                    }
                }catch(\Exception $e){
                    $message = $e->getMessage();
                    info($message);
                    DB::rollback();
                    $response = [
                        'status'    => 500,
                        'message'   => $message
                    ];
                }
            }
        }else{
            $response = [
                'status'    => 401,
                'message'   => 'Token tidak ditemukan'
            ];
        }

        return response()->json($response);
    }

    public function getData(Request $request) {
        $cek = User::where('api_token',$request->bearerToken())->first();
        if($cek && $request->bearerToken()){
            $query = MitraMarketingOrder::where('document_no',$request->document_no)->where('user_id',$cek->id)->first();
            if($query){
                $details = [];

                foreach($query->mitraMarketingOrderDetail as $row){
                    $details[] = [
                        'item'          => $row->item->code.' - '.$row->item->name,
                        'qty'           => round($row->qty,2),
                        'price'         => round($row->price,2),
                        'percent_tax'   => round($row->percent_tax,2),
                        'final_price'   => round($row->final_price,2),
                        'total'         => round($row->total,2),
                        'tax'           => round($row->tax,2),
                        'grandtotal'    => round($row->grandtotal,2),
                        'note'          => $row->note,
                    ];
                }

                $data = [
                    'code'              => $query->code,
                    'customer'          => $query->account->name,
                    'post_date'         => $query->post_date,
                    'valid_date'        => $query->valid_date,
                    'document_no'       => $query->document_no,
                    'type_delivery'     => $query->deliveryType(),
                    'delivery_date'     => $query->delivery_date,
                    'delivery_address'  => $query->delivery_address,
                    'delivery_province' => $query->deliveryProvince->name,
                    'delivery_city'     => $query->deliveryCity->name,
                    'delivery_district' => $query->deliveryDistrict->name,
                    'payment_type'      => $query->paymentType(),
                    'note'              => $query->note,
                    'status'            => $query->statusRaw(),
                    'total'             => round($query->total,2),
                    'tax'               => round($query->tax,2),
                    'grandtotal'        => round($query->grandtotal,2),
                    'details'           => $details,
                ];
                $response = [
                    'status'    => 200,
                    'data'      => $data,
                ];
            }else{
                $response = [
                    'status'    => 401,
                    'message'   => 'Data tidak ditemukan.'
                ];
            }
        }else{
            $response = [
                'status'    => 401,
                'message'   => 'Token tidak ditemukan'
            ];
        }

        return response()->json($response);
    }

    public function getDataAll(Request $request) {
        $cek = User::where('api_token',$request->bearerToken())->first();
        if($cek && $request->bearerToken()){
            $page = $request->page ? $request->page * 25 : 0;
            $querydata = MitraMarketingOrder::where('user_id',$cek->id)->orderByDesc('code')->offset($page)->limit(25)->get();
            if(count($querydata) > 0){
                $data = [];
                foreach($querydata as $query){
                    $details = [];
                    foreach($query->mitraMarketingOrderDetail as $row){
                        $details[] = [
                            'item'          => $row->item->code.' - '.$row->item->name,
                            'qty'           => round($row->qty,2),
                            'price'         => round($row->price,2),
                            'percent_tax'   => round($row->percent_tax,2),
                            'final_price'   => round($row->final_price,2),
                            'total'         => round($row->total,2),
                            'tax'           => round($row->tax,2),
                            'grandtotal'    => round($row->grandtotal,2),
                            'note'          => $row->note,
                        ];
                    }
                    $data[] = [
                        'code'              => $query->code,
                        'customer'          => $query->account->name,
                        'post_date'         => $query->post_date,
                        'valid_date'        => $query->valid_date,
                        'document_no'       => $query->document_no,
                        'type_delivery'     => $query->deliveryType(),
                        'delivery_date'     => $query->delivery_date,
                        'delivery_address'  => $query->delivery_address,
                        'delivery_province' => $query->deliveryProvince->name,
                        'delivery_city'     => $query->deliveryCity->name,
                        'delivery_district' => $query->deliveryDistrict->name,
                        'payment_type'      => $query->paymentType(),
                        'note'              => $query->note,
                        'status'            => $query->statusRaw(),
                        'total'             => round($query->total,2),
                        'tax'               => round($query->tax,2),
                        'grandtotal'        => round($query->grandtotal,2),
                        'details'           => $details,
                    ];
                }
                
                $response = [
                    'status'        => 200,
                    'total_data'    => count($querydata),
                    'page'          => $request->page,
                    'data'          => $data,
                ];
            }else{
                $response = [
                    'status'    => 401,
                    'message'   => 'Data tidak ditemukan.'
                ];
            }
        }else{
            $response = [
                'status'    => 401,
                'message'   => 'Token tidak ditemukan'
            ];
        }

        return response()->json($response);
    }

    public function destroy(Request $request) {
        $cek = User::where('api_token',$request->bearerToken())->first();
        if($cek && $request->bearerToken()){
            $query = MitraMarketingOrder::where('document_no',$request->document_no)->where('user_id',$cek->id)->whereIn('status',['1'])->first();
            if($query){
                if($query->delete()){
                    $query->mitraMarketingOrderDetail()->delete();
                    $response = [
                        'status'    => 200,
                        'message'   => 'Data berhasil dihapus.',
                    ];
                }else{
                    $response = [
                        'status'    => 401,
                        'message'   => 'Data tidak ditemukan.'
                    ];
                }
            }else{
                $response = [
                    'status'    => 401,
                    'message'   => 'Data tidak ditemukan / status dokumen diluar perubahan.'
                ];
            }
        }else{
            $response = [
                'status'    => 401,
                'message'   => 'Token tidak ditemukan'
            ];
        }

        return response()->json($response);
    }
}