<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
        $cek = User::where('api_token',$request->bearerToken())->count();
        if($cek > 0){
            $validation = Validator::make($request->all(), [
                'document_no'       => 'required|unique:mitra_marketing_orders,document_no',
                'customer_code'     => 'required',
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
                'document_no.unique'         => 'Kode dokumen penghubung telah dipakai.',
                'customer_code.required'     => 'Kode Customer tidak boleh kosong.',
                'type.required'              => 'Tipe SO tidak boleh kosong.',
                'post_date.required'         => 'Tanggal post tidak boleh kosong.',
                'valid_date.required'        => 'Tanggal valid SO tidak boleh kosong.',
                'type_delivery.required'     => 'Tipe pengiriman tidak boleh kosong.',
                'delivery_date.required'     => 'Tanggal permintaan pengiriman tidak boleh kosong.',
                'delivery_schedule.required' => 'Status pengiriman tidak boleh kosong.',
                'payment_type.required'      => 'Tipe pembayaran tidak boleh kosong.',
                'dp_type.required'           => 'Tipe DP tidak boleh kosong.',
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
                $response = [
                    'status'    => 200,
                    'message'   => $validation->errors()
                ];
            }
        }else{
            $response = [
                'status'    => 500,
                'message'   => 'Token tidak ditemukan'
            ];
        }

        return response()->json($response);
    }
}