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

class StockController extends Controller
{

    public function getDataAll(Request $request)
    {
        $cek = User::where('api_token', $request->bearerToken())->first();

        if($cek && $request->bearerToken()) {
            $brand_id = $cek->brand_id;
            if($brand_id == null || $brand_id == ''){
                return apiResponse(false, 422, "Data tidak ditemukan", null, []);
            }

            $query = DB::select("
            SELECT a.kode,a.name,a.shading, a.initial as stock FROM (
        SELECT kode,a.name,a.shading, SUM(qty) AS initial FROM (

        SELECT  d.code AS kode,d.name,k.code AS shading, coalesce(SUM(b.qty*c.conversion),0) AS Qty
            FROM production_handovers a
            LEFT JOIN production_handover_details b ON a.id=b.production_handover_id
            LEFT JOIN production_fg_receive_details c ON c.id=b.production_fg_receive_detail_id and c.deleted_at IS null
            LEFT JOIN items d ON d.id=b.item_id
            LEFT JOIN item_shadings k ON k.id=b.item_shading_id
        WHERE d.brand_id =" . $brand_id . " and a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7 
        GROUP BY d.code,d.name,k.code
        UNION ALL
        SELECT d.code AS kode,d.name,k.code, coalesce(SUM(b.qty),0)*-1 AS RepackOut
            FROM production_repacks a
        LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
        LEFT JOIN item_units c ON c.id=item_unit_source_id
        LEFT JOIN items d ON d.id=b.item_source_id
            LEFT JOIN item_shadings k ON k.id=b.item_shading_id
            WHERE d.brand_id =" . $brand_id . " and a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id AND d.item_group_id=7  
        GROUP BY d.code,d.name,k.code
        UNION ALL
        SELECT d.code AS kode,d.name,k.code, coalesce(SUM(b.qty),0) AS RepackIn
            FROM production_repacks a
        LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
        LEFT JOIN item_units c ON c.id=item_unit_target_id
        LEFT JOIN items d ON d.id=b.item_target_id
        LEFT JOIN item_shadings k ON k.id=b.item_shading_id
            WHERE d.brand_id =" . $brand_id . " and a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  
        GROUP BY d.code,d.name,k.code
        UNION ALL
        SELECT d.code AS kode,d.name,k.code, coalesce(SUM(b.qty),0) AS GR
        FROM good_receives a
        LEFT JOIN good_receive_details b ON a.id=b.good_receive_id and b.deleted_at is null
        LEFT JOIN items d ON d.id=b.item_id
        LEFT JOIN item_shadings k ON k.id=b.item_shading_id
            WHERE d.brand_id =" . $brand_id . " and a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  
        GROUP BY d.code,d.name,k.code
        UNION ALL
        SELECT d.`code` AS kode,d.name,k.code, coalesce(SUM(b.qty),0)*-1 AS GI
        FROM good_issues a
        LEFT JOIN good_issue_details b ON a.id=b.good_issue_id and b.deleted_at is null
        LEFT JOIN item_stocks c ON c.id=b.item_stock_id
        LEFT JOIN items d ON d.id=c.item_id
        LEFT JOIN item_shadings k ON k.id=b.item_shading_id
            WHERE d.brand_id =" . $brand_id . " and a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7 
        GROUP BY d.code,d.name,k.code
        UNION ALL
        SELECT c.code AS kode,c.name,k.code, coalesce(SUM(b.qty*f.qty_conversion),0)*-1 AS qtySJ
            FROM marketing_order_delivery_processes a
            LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id
            LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
            LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
            LEFT JOIN item_stocks l ON l.id=b.item_stock_id
            LEFT JOIN items c ON c.id=e.item_id
        LEFT JOIN item_shadings k ON k.id=l.item_shading_id
            WHERE c.brand_id =" . $brand_id . " and a.void_date is null AND a.deleted_at is NULL AND c.item_group_id=7  
        GROUP BY c.code,c.name,k.code)a GROUP BY kode,NAME,shading)a

        ");


            if ($query) {
                $data = [];

                foreach ($query as $row) {
                    $data[] = [
                        'code'          => $row->kode,
                        'name'           => $row->name,
                        'shading'           => $row->shading,
                        'qty'         => round($row->stock, 2),
                        'uom' => 'M2'
                    ];
                }


                return apiResponse(true, 200, 'Data Ditemukan', $data, []);
            } else {
                return apiResponse(true, 200, 'Data tidak ditemukan', '', []);
            }
        } else {
            return apiResponse(false, 401, 'Token tidak valid', null, []);
        }

        return response()->json($response);
    }

    public function getData(Request $request)
    {
        $cek = User::where('api_token', $request->bearerToken())->first();

        if($cek && $request->bearerToken()){
            $brand_id = $cek->brand_id;
            if($brand_id == null || $brand_id == ''){
                return apiResponse(false, 400, "Data mitra tidak ditemukan", null, []);
            }

            $code = $request->code;
            if($code == null || $code == ''){
                return apiResponse(false, 400, "Kode item harus dikirim", null, []);
            }

            $query = DB::select("
            SELECT a.kode,a.name,a.shading, a.initial as stock FROM (
        SELECT kode,a.name,a.shading, SUM(qty) AS initial FROM (

        SELECT  d.code AS kode,d.name,k.code AS shading, coalesce(SUM(b.qty*c.conversion),0) AS Qty
            FROM production_handovers a
            LEFT JOIN production_handover_details b ON a.id=b.production_handover_id
            LEFT JOIN production_fg_receive_details c ON c.id=b.production_fg_receive_detail_id and c.deleted_at IS null
            LEFT JOIN items d ON d.id=b.item_id
            LEFT JOIN item_shadings k ON k.id=b.item_shading_id
        WHERE d.code='" . $code . "' and d.brand_id =" . $brand_id . " and a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7 
        GROUP BY d.code,d.name,k.code
        UNION ALL
        SELECT d.code AS kode,d.name,k.code, coalesce(SUM(b.qty),0)*-1 AS RepackOut
            FROM production_repacks a
        LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
        LEFT JOIN item_units c ON c.id=item_unit_source_id
        LEFT JOIN items d ON d.id=b.item_source_id
            LEFT JOIN item_shadings k ON k.id=b.item_shading_id
            WHERE d.code='" . $code . "' and d.brand_id =" . $brand_id . " and a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id AND d.item_group_id=7  
        GROUP BY d.code,d.name,k.code
        UNION ALL
        SELECT d.code AS kode,d.name,k.code, coalesce(SUM(b.qty),0) AS RepackIn
            FROM production_repacks a
        LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
        LEFT JOIN item_units c ON c.id=item_unit_target_id
        LEFT JOIN items d ON d.id=b.item_target_id
        LEFT JOIN item_shadings k ON k.id=b.item_shading_id
            WHERE d.code='" . $code . "' and d.brand_id =" . $brand_id . " and a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  
        GROUP BY d.code,d.name,k.code
        UNION ALL
        SELECT d.code AS kode,d.name,k.code, coalesce(SUM(b.qty),0) AS GR
        FROM good_receives a
        LEFT JOIN good_receive_details b ON a.id=b.good_receive_id and b.deleted_at is null
        LEFT JOIN items d ON d.id=b.item_id
        LEFT JOIN item_shadings k ON k.id=b.item_shading_id
            WHERE d.code='" . $code . "' and d.brand_id =" . $brand_id . " and a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  
        GROUP BY d.code,d.name,k.code
        UNION ALL
        SELECT d.`code` AS kode,d.name,k.code, coalesce(SUM(b.qty),0)*-1 AS GI
        FROM good_issues a
        LEFT JOIN good_issue_details b ON a.id=b.good_issue_id and b.deleted_at is null
        LEFT JOIN item_stocks c ON c.id=b.item_stock_id
        LEFT JOIN items d ON d.id=c.item_id
        LEFT JOIN item_shadings k ON k.id=b.item_shading_id
            WHERE d.code='" . $code . "' and d.brand_id =" . $brand_id . " and a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7 
        GROUP BY d.code,d.name,k.code
        UNION ALL
        SELECT c.code AS kode,c.name,k.code, coalesce(SUM(b.qty*f.qty_conversion),0)*-1 AS qtySJ
            FROM marketing_order_delivery_processes a
            LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id
            LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
            LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
            LEFT JOIN item_stocks l ON l.id=b.item_stock_id
            LEFT JOIN items c ON c.id=e.item_id
        LEFT JOIN item_shadings k ON k.id=l.item_shading_id
            WHERE c.code='" . $code . "' and c.brand_id =" . $brand_id . " and a.void_date is null AND a.deleted_at is NULL AND c.item_group_id=7  
        GROUP BY c.code,c.name,k.code)a GROUP BY kode,NAME,shading)a

        ");

            if ($query) {
                $data = [];

                foreach ($query as $row) {
                    $data[] = [
                        'code'          => $row->kode,
                        'name'           => $row->name,
                        'shading'           => $row->shading,
                        'qty'         => round($row->stock, 2),
                        'uom' => 'M2'
                    ];
                }


                $response = [
                    'status'    => 200,
                    'data'      => $data,
                ];
            } else {
                $response = [
                    'status'    => 401,
                    'message'   => 'Data tidak ditemukan.'
                ];
            }
        } else {
            $response = [
                'status'    => 401,
                'message'   => 'Token tidak ditemukan'
            ];
        }

        return response()->json($response);
    }
}
