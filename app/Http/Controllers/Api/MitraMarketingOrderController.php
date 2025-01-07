<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Menu;
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
        if($cek){
            $validation = Validator::make($request->all(), [
                'document_no'       => 'required',
                'customer_code'     => 'required',
                'branch_code'       => 'required',
                'type'              => 'required',
                'post_date'         => 'required',
                'valid_date'        => 'required',
                'type_delivery'     => 'required',
                'delivery_date'     => 'required',
                'delivery_schedule' => 'required',
                'payment_type'      => 'required',
                'dp_type'           => $request->payment_type == '1' ? 'required' : '',
                'total'             => 'required',
                'tax'               => 'required',
                'grandtotal'        => 'required',
                'details'           => 'required|array',
            ], [
                'document_no.required'       => 'Kode dokumen penghubung tidak boleh kosong.',
                'customer_code.required'     => 'Kode Customer tidak boleh kosong.',
                'branch_code.required'       => 'Kode Branch tidak boleh kosong.',
                'type.required'              => 'Tipe SO tidak boleh kosong. 1 : proyek, 2 : retail, 3 : khusus, 4 : sample.',
                'post_date.required'         => 'Tanggal post tidak boleh kosong.',
                'valid_date.required'        => 'Tanggal valid SO tidak boleh kosong.',
                'type_delivery.required'     => 'Tipe pengiriman tidak boleh kosong. 1 : loco, 2 : franco.',
                'delivery_date.required'     => 'Tanggal permintaan pengiriman tidak boleh kosong.',
                'delivery_schedule.required' => 'Status pengiriman tidak boleh kosong. 1 : Segera, 2 : Terjadwal, 3 : Belum Terjadwal.',
                'payment_type.required'      => 'Tipe pembayaran tidak boleh kosong. 1 : DP, 2 : Kredit.',
                'dp_type.required'           => 'Tipe DP tidak boleh kosong. 1 : Proporsional, 2 : FIFO.',
                'total.required'             => 'Total tidak boleh kosong.',
                'tax.required'               => 'Pajak tidak boleh kosong.',
                'grandtotal.required'        => 'Grandtotal tidak boleh kosong.',
                'details.required'           => 'Detail item tidak boleh kosong.',
                'details.array'              => 'Detail harus dalam bentuk array.',
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
                    $customer = User::where('employee_no',$request->customer_code)->where('status','1')->where('type','2')->first();
                    if(!$customer){
                        $errorMessage[] = 'Customer tidak ditemukan.';
                    }

                    if(!in_array($request->type,['1','2','3','4'])){
                        $errorMessage[] = 'Tipe SO harap diisikan "1" : Proyek / "2" : Retail / "3" : Khusus / "4" : Sample.';
                    }

                    if(!in_array($request->type_delivery,['1','2'])){
                        $errorMessage[] = 'Tipe Pengiriman harap diisikan "1" : Loco / "2" : Franco';
                    }

                    if(!in_array($request->delivery_schedule,['1','2'])){
                        $errorMessage[] = 'Status pembelian harap diisikan "1" : Segera / "2" : Terjadwal / "3" : Belum Terjadwal.';
                    }

                    if(!in_array($request->payment_type,['1','2'])){
                        $errorMessage[] = 'Status pembelian harap diisikan "1" : DP / "2" : Kredit.';
                    }

                    if($request->payment_type == '1'){
                        if(!in_array($request->dp_type,['1','2'])){
                            $errorMessage[] = 'Status pembelian harap diisikan "1" : Proporsional, "2" : FIFO.';
                        }
                    }

                    foreach($request->details as $key => $row){
                        $item = null;
                        $item = Item::where('code',$row['item_code'])->where('status','1')->first();
                        if(!$item){
                            $errorMessage[] = 'Item dengan kode '.$row['item_code'].' tidak ditemukan.';
                        }
                    }

                    if(count($errorMessage) == 0){
                        $querycheck = MitraMarketingOrder::where('document_no',$request->document_no)->whereIn('status',['1','2','3'])->first();

                        if(!$querycheck){
                            $query = MitraMarketingOrder::create([
                                'code'          => $newCode,
                                'user_id'       => $cek->id,
                                'account_id'    => $customer->id,
                                'type'          => $request->type,
                                'post_date'     => $request->post_date,
                                'valid_date'    => $request->valid_date,
                                'document_no'   => $request->document_no,
                                'type_delivery' => $request->type_delivery,
                                'payment_type'  => $request->payment_type,
                                'dp_type'       => $request->dp_type ?? NULL,
                                'note'          => $request->note,
                                'total'         => $request->total,
                                'tax'           => $request->tax,
                                'grandtotal'    => $request->grandtotal,
                                'status'        => '1',
                            ]);
    
                            foreach($request->details as $key => $row){
                                $item = Item::where('code',$row['item_code'])->where('status','1')->first(); 
                                MitraMarketingOrderDetail::create([
                                    'mitra_marketing_order_id' => $query->id,
                                    'item_id'                  => $item->id,
                                    'qty'                      => $row['qty'],
                                    'price'                    => $row['price'],
                                    'percent_tax'              => $row['percent_tax'],
                                    'final_price'              => $row['final_price'],
                                    'total'                    => $row['total'],
                                    'tax'                      => $row['tax'],
                                    'grandtotal'               => $row['grandtotal'],
                                    'note'                     => $row['note'],
                                ]);
                            }
                            
                            $response = [
                                'status'    => 200,
                                'message'   => 'Data berhasil disimpan'
                            ];
                        }else{
                            $response = [
                                'status'    => 500,
                                'message'   => 'Dokumen penghubung telah masuk ke dalam sistem. Silahkan cek status dokumen.',
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
}