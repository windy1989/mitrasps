<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MitraCustomer;
use App\Models\User;
use App\Models\Item;
use App\Models\MitraComplaintSales;
use App\Models\MitraComplaintSalesDetail;
use App\Models\MarketingOrderDelivery;
use App\Models\MarketingOrderDeliveryDetail;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\MarketingOrderDeliveryProcessDetail;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MitraComplaintSalesController extends Controller
{
    //
    public function index(){

    }

    //get all mitra complaint sales data 
    public function getDataAll(Request $request){
        $cek = User::where('api_token',$request->bearerToken())->first();

        if($cek && $request->bearerToken()){
            //query param
            $offset = $request->query('offset', 0);
            $limit  = $request->query('limit', 100); 
            
            $query_data = MitraComplaintSales::where('mitra_id', $cek->id)
                        ->offset($offset)->limit($limit)->get();
            
            if(count($query_data) > 0){
                foreach($query_data as $row){
                    $detail_item  = MitraComplaintSalesDetail::where('mitra_complaint_sales_id', $row->id)->get();
                    $items= [];
                    if($detail_item){
                        foreach($detail_item as $item){
                            $items[] = [
                                'item_code'         => optional($item->item)->code,
                                'item_name'         => optional($item->item)->name,
                                'batch_no'          => $item->batch_code,
                                'note'              => $item->note,
                                'qty_wrong_color'   => round($item->qty_color_mistake, 2),
                                'qty_wrong_pattern' => round($item->qty_motif_mistake, 2),
                                'qty_wrong_size'    => round($item->qty_size_mistake, 2),
                                'qty_broken'        => round($item->qty_broken, 2),
                                'qty_mistake'       => round($item->qty_mistake, 2),
                            ];
                        }
                    }
                    
                    $data[] = [
                        'delivery_order_code' => $row->delivery_order_code,
                        'customer_code'       => $row->customer->mitraCustomer->code,
                        'customer_name'       => $row->customer->name,
                        'sales_name'          => $row->sales_name,
                        'note'                => $row->note,
                        // 'document'            => explode(',',$row->document),
                        'status'              => $row->statusRaw(),
                        'detail_item'         => $items,
                    ];
                }
                return apiResponse(true, 200, 'Data komplain ditampilkan', $data, []);
            }else{
                return apiResponse(true, 200, 'Data komplain tidak ditemukan', [], []);
            }
        }else{
            return apiResponse(false, 401, 'Token tidak valid', null, []);
        }
    }

    //get single mitra complaint sales data
    public function getData(Request $request, $code){
        $cek = User::where('api_token',$request->bearerToken())->first();
        if($cek && $request->bearerToken()){
            //path param
            $code = base64_decode($code);

            $mitra_complaint = MitraComplaintSales::where('mitra_id', $cek->id)->where('delivery_order_code', $code)->first();
            if($mitra_complaint){
                $detail_item    = MitraComplaintSalesDetail::where('mitra_complaint_sales_id', $mitra_complaint->id)->get();
                    $items= [];
                    if($detail_item){
                        foreach($detail_item as $item){
                            $items[] = [
                                'item_code'         => optional($item->item)->code,
                                'item_name'         => optional($item->item)->name,
                                'batch_no'          => $item->batch_code,
                                'note'              => $item->note,
                                'qty_wrong_color'   => round($item->qty_color_mistake, 2),
                                'qty_wrong_pattern' => round($item->qty_motif_mistake, 2),
                                'qty_wrong_size'    => round($item->qty_size_mistake, 2),
                                'qty_broken'        => round($item->qty_broken, 2),
                                'qty_mistake'       => round($item->qty_mistake, 2),
                            ];
                        }
                    }
                    
                    $data[] = [
                        'delivery_order_code' => $mitra_complaint->delivery_order_code,
                        'customer_code'       => $mitra_complaint->customer->mitraCustomer->code,
                        'customer_name'       => $mitra_complaint->customer->name,
                        'sales_name'          => $mitra_complaint->sales_name,
                        'note'                => $mitra_complaint->note,
                        // 'document'            => explode(',',$row->document),
                        'status'              => $mitra_complaint->statusRaw(),
                        'detail_item'         => $items,
                    ];
            
                return apiResponse(true, 200, 'Data komplain ditampilkan', $data, []);
            }else{
                return apiResponse(true, 200, 'Data komplain tidak ditemukan', null, []);
            }
        }else{
            return apiResponse(false, 401, 'Token tidak valid', null, []);
        }
    }

    public function create(Request $request){
        $cek = User::where('api_token',$request->bearerToken())->first();
        if($cek && $request->bearerToken()){
            //path param

            $validation = Validator::make($request->all(), [
                'delivery_order_code' => 'required',
                'customer_code'       => 'required',
                'qty_delivered'       => 'required|integer',
                'sales_name'          => 'required',
                'document.*'          => 'required|file|mimes:jpg,jpeg,png|max:2048',
                'note'                => 'required',
                'detail_item'         => 'required',
            ], [
                'delivery_order_code.required' => 'Nomor Surat Jalan tidak boleh kosong.',
                'customer_code.required'       => 'Customer tidak boleh kosong.',
                'qty_delivered.required'       => 'Qty Terkirim tidak boleh kosong.',
                'qty_delivered.integer'        => 'Qty Terkirim harus berupa angka.',
                'sales_name.required'          => 'Nama Sales tidak boleh kosong.',
                'document.*.required'          => 'Foto tidak boleh kosong.',
                'document.*.file'              => 'Foto harus berupa file gambar.',
                'document.*.mimes'             => 'Format file harus berupa .jpg, .jpeg, .png',
                'document.*.max'               => 'File foto harus berukuran di bawah 2MB',
                'note.required'                => 'Keterangan tidak boleh kosong.',
                'detail_item.*.required'       => 'Detail item tidak boleh kosong.',
            ]);

            if($validation->fails()) {
                return apiResponse(false, 422, "Data yang dikirimkan tidak valid.", $validation->errors(), []);
            } else {
                $mitra_complaint = MitraComplaintSales::where('mitra_id',$cek->id)->where('delivery_order_code', $request->delivery_order_code)->first();
                if($mitra_complaint){
                    return apiResponse(false, 404, "Kode SJ ini sudah pernah dikomplain.", null, []);
                }

                DB::beginTransaction();
                try {
                    //Cek Mitra Customer
                    $mitra_customer = MitraCustomer::where('mitra_id',$cek->id)->where('code',$request->customer_code)->first();
                    if(!$mitra_customer){
                        return apiResponse(false, 404, "Kode Customer tidak ditemukan", null, []);
                    }
                    if($mitra_customer->status_approval != '1'){
                        return apiResponse(false, 404, "Customer sedang dalam tahap ".$mitra_customer->statusApprovalRaw(), null, []);
                    }

                    //Cek Surat Jalan / MODP
                    $modp = MarketingOrderDeliveryProcess::where('code', $request->delivery_order_code)->whereNull('deleted_at')->first();
                    if(!$modp){
                        return apiResponse(false, 404, "Kode SJ tidak ditemukan", null, []);
                    }
                    
                    //Cek Kode Customer dengan Customer pada Surat Jalan
                    $customer_modp = $modp->marketingOrderDelivery->customer;
                    if(optional($mitra_customer->user)->id <> $customer_modp->id){
                        return apiResponse(false, 404, "Kode Customer tidak valid untuk SJ ini", null, []);
                    }

                    //Cek Item valid dengan surat jalan
                    if($request->detail_item){
                        $arritemMOD = MarketingOrderDeliveryDetail::select('item_id')->where('marketing_order_delivery_id', $modp->marketing_order_delivery_id)->get()->toArray();  //Get list item MOD
                        $arritemId = array_map(fn($item) => $item['item_id'], $arritemMOD); //List ID Item dari item MOD

                        $errorMessage = []; $ctr = 0;
                        foreach($request->detail_item as $item){   //Loop item yang dikirim lewat API
                            $ctr++;
                            $namaitem = '';
                            //Cek array item apakah lengkap
                            if(!array_key_exists('code', $item)){
                                $namaitem = 'ke-'.$ctr;
                                $errorMessage[] = "Cek item ".$namaitem.", properti 'code' tidak ada.";
                            }
                            else{
                                $namaitem = $item['code'];
                                //Kalau array item code ada
                                $temp_item = Item::where('code', $item['code'])->first();
                                if(!in_array(optional($temp_item)->id, $arritemId)){    //Cek kalau item di API tidak ada di MOD itu. 
                                    $errorMessage[] = "Item dengan kode '".$item['code']."' tidak ada di Surat Jalan ini";
                                }
                            }

                            if(!array_key_exists('batch_no', $item)){
                                $errorMessage[] = "Cek item ".$namaitem.", properti 'batch_no' tidak ada.";
                            }
                            if(!array_key_exists('note', $item)){
                                $errorMessage[] = "Cek item ".$namaitem.", properti 'note' tidak ada.";
                            }
                            if(!array_key_exists('qty_wrong_color', $item)){
                                $errorMessage[] = "Cek item ".$namaitem.", properti 'qty_wrong_color' tidak ada.";
                            }
                            if(!array_key_exists('qty_wrong_pattern', $item)){
                                $errorMessage[] = "Cek item ".$namaitem.", properti 'qty_wrong_pattern' tidak ada.";
                            }
                            if(!array_key_exists('qty_wrong_size', $item)){
                                $errorMessage[] = "Cek item ".$namaitem.", properti 'qty_wrong_size' tidak ada.";
                            }
                            if(!array_key_exists('qty_broken', $item)){
                                $errorMessage[] = "Cek item ".$namaitem.", properti 'qty_broken' tidak ada.";
                            }
                            if(!array_key_exists('qty_mistake', $item)){
                                $errorMessage[] = "Cek item ".$namaitem.", properti 'qty_mistake' tidak ada.";
                            }
                        }
                        if(count($errorMessage)>0){
                            return apiResponse(false, 404, "Item tidak valid", $errorMessage, []);
                        }
                    }
                    else{
                        return apiResponse(false, 400, "Komplain tidak dapat diproses", null, []); //Item tidak ada di detail
                    }
                    //End cek item

                    //Cek foto/dokumen komplain
                    $ctr=0;
                    if($request->hasFile('document')){
                        $arrFile = [];
                        foreach($request->file('document') as $file){
                            $arrFile[] = $file->store('public/mitra_complaints');
                            $ctr++;
                        }
                        //Cek jumlah foto
                        if($ctr>2){
                            foreach($arrFile as $file){
                                if(Storage::exists($file)){
                                    Storage::delete($file);
                                    // unlink(public_path("storage/").$file);
                                }
                            }
                            return apiResponse(false, 400, "File foto yang dapat diupload maximum 3.", null, []);
                        }
                    }
                    else{
                        return apiResponse(false, 400, "Komplain tidak dapat diproses", null, []); //Tidak ada foto
                    }
                    //End cek foto/dokumen

                    return apiResponse(true, 418, "XXXX", null, []);

                    //Create header komplain
                    $query = MitraComplaintSales::create([
                        'delivery_order_code' => $request->delivery_order_code,
                        'delivery_order_id'   => $modp->id,
                        'customer_id'         => $mitra_customer->user->id,
                        'qty_delivered'       => $request->qty_delivered,
                        'sales_name'          => $request->sales_name,
                        'document'            => $arrFile ? implode(',', $arrFile) : '',
                        'note'                => $request->note,
                        'mitra_id'            => $cek->id,
                        'status'              => '1',
                    ]);

                    //Create detail komplain
                    $cek_kode_item = [];
                    foreach($request->detail_item as $item){
                        //Cek Item kembar
                        $temp_item = Item::where('code', $item['code'])->first();
                        if(in_array($item['code'],$cek_kode_item)){
                            return apiResponse(false, 400, "Kode item ".$item['kembar']." kembar", null, []);
                        }
                        else{
                            $cek_kode_item[] = $item['code'];
                        }

                        MitraComplaintSalesDetail::create([
                            'mitra_complaint_sales_id' => $query->id,
                            'item_id'                  => $temp_item->id,
                            'batch_code'               => $item['batch_no'],
                            'note'                     => $item['note'],
                            'qty_color_mistake'        => $item['qty_wrong_color'],
                            'qty_motif_mistake'        => $item['qty_wrong_pattern'],
                            'qty_size_mistake'         => $item['qty_wrong_size'],
                            'qty_broken'               => $item['qty_broken'],
                            'qty_mistake'              => $item['qty_mistake'],
                        ]);
                    }
                    

                    DB::commit();
                    return apiResponse(true, 201, "Data komplain berhasil disimpan", null, []);
                }
                catch(\Exception $e){
                    DB::rollback();
                    info($e);
                    $message = $e->getMessage();
                    return apiResponse(false, 400, "Data gagal disimpan" , $message, []);
                }
            }
        }else{
            return apiResponse(false, 401, 'Token tidak valid', null, []);
        }
    }
}
