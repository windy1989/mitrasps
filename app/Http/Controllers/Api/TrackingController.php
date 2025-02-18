<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class TrackingController extends Controller
{

    public function getData(Request $request){
        $cek = User::where('api_token', $request->bearerToken())->first();

        if($cek && $request->bearerToken()) {
            $tracking = [];

            $mitramocode = $request->code;
            
            $mitramarketingorder = '';
            $mitrasalesorder = '';
            $mitramod = '';
            $mitrasj = '';

            //mitra praso
            $querymitramarketingorder = DB::select("
                select distinct created_at from mitra_marketing_orders 
                where code='" . $mitramocode . "' and void_date is null and deleted_at is null
            ");

            if ($querymitramarketingorder) {
                foreach ($querymitramarketingorder as $row) {
                    $tracking[] = [
                        'document' => 'Sales Order Mitra',
                        'code'     => $mitramocode,
                        'date'     => $row->created_at,
                    ];
                }
            }

            //mitra so
            $querymitraso = DB::select("
                select distinct a.code, a.created_at from marketing_orders a 
                left join mitra_marketing_orders b on a.document_no=b.code and b.deleted_at is null 
                where b.code='" . $mitramocode . "' and a.void_date is null and a.deleted_at is null
            ");

            if ($querymitraso) {
                foreach ($querymitraso as $row) {
                    $tracking[] = [
                        'document' => 'Sales Order',
                        'code'     => $row->code,
                        'date'     => $row->created_at,
                    ];
                }
            }
            //mitra mod
            $querymitramod = DB::select("
                select distinct a.code,a.created_at from marketing_order_deliveries a 
                left join marketing_order_delivery_details b on a.id=b.marketing_order_delivery_id and b.deleted_at is null
                left join marketing_order_details c on c.id=b.marketing_order_detail_id and c.deleted_at is null
                left join marketing_orders d on d.id=c.marketing_order_id and d.deleted_at is null and d.void_date is null
                where d.document_no='" . $mitramocode . "' and a.void_date is null and a.deleted_at is null
            ");
            
            if ($querymitramod) {
                foreach ($querymitramod as $row) {
                    $tracking[] = [
                        'document' => 'Jadwal Pengiriman',
                        'code'     => $row->code,
                        'date'     => $row->created_at,
                    ];
                }
            }

            //mitra sj
            $querymitrasj = DB::select("
                select distinct a.id,a.code,a.created_at from marketing_order_delivery_processes a 
                left join marketing_order_delivery_process_details b on a.id=b.marketing_order_delivery_process_id
                left join marketing_order_delivery_details c on c.id=b.marketing_order_delivery_detail_id and c.deleted_at is null
                left join marketing_order_deliveries d on d.id=c.marketing_order_delivery_id and d.deleted_at is null and d.void_date is null
                left join marketing_order_details e on e.id=c.marketing_order_detail_id and e.deleted_at is null
                left join marketing_orders f on f.id=e.marketing_order_id and f.deleted_at is null and f.void_date is null
                where f.document_no='" . $mitramocode . "' and a.void_date is null and a.deleted_at is null
            ");

            if ($querymitrasj) {
                foreach ($querymitrasj as $row) {
                    $mitrasj = $row->id;
                    $tracking[] = [
                        'document' => 'Surat Jalan',
                        'code'     => $row->code,
                        'date'     => $row->created_at,
                    ];
                }
            }

            //mitra sj keluar pabrik
            $querymitrasatpam = DB::select("
                select distinct created_at from marketing_order_delivery_process_tracks
                where marketing_order_delivery_process_id='" . $mitrasj . "' and deleted_at is null and status=1
            ");

            if ($querymitrasatpam) {
                foreach ($querymitrasatpam as $row) {
                    $tracking[] = [
                        'status' => 'Keluar Pabrik',
                        'date'   => $row->created_at,
                    ];
                }
            }

            $tracking = json_decode(json_encode($tracking));
            if ($tracking) {
                return apiResponse(true, 200, 'Data dokumen ditemukan', $tracking, []);
            } else {
                return apiResponse(false, 404, 'Data dokumen tidak ditemukan', null, []);
            }
        } else {
            return apiResponse(false, 401, 'Token tidak valid', null, []);
        }
    }
}
