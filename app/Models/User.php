<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'photo',
        'signature',
        'name',
        'employee_no',
        'password',
        'username',
        'phone',
        'address',
        'province_id',
        'city_id',
        'district_id',
        'id_card',
        'id_card_address',
        'type',
        'group_id',
        'status',
        'company_id',
        'place_id',
        'position_id',
        'logo',
        'tax_id',
        'tax_name',
        'tax_address',
        'nib',
        'sppkp',
        'pic',
        'pic_position',
        'pic_no',
        'office_no',
        'email',
        'deposit',
        'limit_credit',
        'count_limit_credit',
        'top',
        'top_internal',
        'gender',
        'married_status',
        'married_date',
        'children',
        'last_change_password',
        'country_id',
        'token',
        'connection_id',
        'user_status',
        'user_image',
        'api_token',
        'manager_id',
        'employment_status',
        'employee_type',
        'is_ar_invoice',
        'registration_id',
        'reset_code',
        'type_payment',
        'nib',
        'spkp',
        'id_program_lama',
        'is_special_lock_user',
        'type_body',

        'brand_id',
        'sales_payment_type',

        'sale_area_id',
        'pic_finance',
        'no_pic_finance',
    ];

    protected $hidden = [
        'password',
    ];

    public function needChangePassword(){
        $days = now()->diffInDays(Carbon::parse($this->last_change_password));

        if($days >= 60){
            return true;
        }else{
            return false;
        }
    }

    public function type(){
        $type = match ($this->type) {
          '1' => 'Pegawai',
          '2' => 'Customer',
          '3' => 'Supplier',
          '4' => 'Expedisi',
          '5' => 'Broker',
          default => '',
        };

        return $type;
    }

    public function isSpecial(){
        $type = match ($this->is_special_lock_user) {
          '1' => 'Ya',
          default => 'Tidak',
        };
        return $type;
    }

    public function employeeType(){
        $type = match ($this->employee_type) {
          '1' => 'Staff',
          '2' => 'Non-Staff',
          default => '-',
        };

        return $type;
    }

    public function arInvoice(){
        $arInvoice = match ($this->is_ar_invoice) {
          '1' => 'Ya',
          default => 'Tidak',
        };

        return $arInvoice;
    }

    public function marriedStatus(){
        $marriedStatus = match ($this->married_status) {
          '1' => 'Single',
          '2' => 'Menikah',
          '3' => 'Cerai',
          default => 'Invalid',
        };

        return $marriedStatus;
    }

    public function gender(){
        $gender = match ($this->gender) {
          '1' => 'Pria',
          '2' => 'Wanita',
          '3' => 'Lainnya',
          default => 'Lainnya',
        };

        return $gender;
    }

    public function photo()
    {
        if($this->photo !== NULL && Storage::exists($this->photo)) {
            $document = asset(Storage::url($this->photo));
        } else {
            $document = asset('website/empty_profile.png');
        }

        return $document;
    }

    public function profilePicture()
    {
        return '<span class="avatar-status avatar-online" style="width:50px !important;"><img src="'.$this->photo().'" alt="avatar"></span>';
    }

    public function signature()
    {
        $path_img = '';

        if(Storage::exists($this->signature)){
            $image = storage_path('app/'.$this->signature);
            $extencion = explode('.',$image);
            $base64 = base64_encode(file_get_contents($image));
            $path_img = 'data:image/' . $extencion[1] . ';base64,' . $base64;
        }

        return '<img src="'.$path_img.'" width="70px">';
    }

    public function getStandarPrice($date){
        $total = 0;
        $data = StandardCustomerPrice::where('group_id',$this->group_id)->where('start_date','>=',$date)->where('end_date','<=',$date)->where('status','1')->first();
        if($data){
            $total = $data->price;
        }
        return $total;
    }

    public static function generateCode($type, $kind, $place_id)
    {
        $prefix = '';

        if($type == '1'){
            if($kind){
                if($kind == '1'){
                    $prefix = '3'.date('y');
                    $query = User::withTrashed()->selectRaw('RIGHT(employee_no, 3) as code')
                        ->whereRaw("employee_no LIKE '$prefix%'")
                        ->withTrashed()
                        ->orderByDesc('employee_no')
                        ->limit(1)
                        ->get();
                }elseif($kind == '2'){
                    $prefix = '3'.'.'.$place_id.'-'.date('y').date('m');
                    $query = User::withTrashed()->selectRaw('RIGHT(employee_no, 8) as code')
                        ->whereRaw("employee_no LIKE '$prefix%'")
                        ->withTrashed()
                        ->orderByDesc('employee_no')
                        ->limit(1)
                        ->get();
                }

                if($query->count() > 0) {
                    $code = intval($query[0]->code) + 1;
                } else {
                    $code = '001';
                }

                $no = str_pad($code, 3, 0, STR_PAD_LEFT);

                return $prefix.$no;
            }
        }else{
            if($type == '2'){
                $prefix = 'P';
            }elseif($type == '3'){
                $prefix = 'S';
            }elseif($type == '4'){
                $prefix = 'E';
            }elseif($type == '5'){
                $prefix = 'B';
            }

            $query = User::withTrashed()->selectRaw('type, RIGHT(employee_no, 6) as code')
                ->where('employee_no','like', "$prefix%")
                ->where('type',$type)
                ->orderByDesc('employee_no')
                ->limit(1)
                ->get();

            if($query->count() > 0) {
                $code = intval($query[0]->code) + 1;
            } else {
                $code = '000001';
            }

            $no = str_pad($code, 6, 0, STR_PAD_LEFT);

            return $prefix.$no;
        }
    }

    public function status(){
        switch($this->status) {
            case '1':
                $status = '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>';
                break;
            case '2':
                $status = '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>';
                break;
            default:
                $status = '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>';
                break;
        }

        return $status;
    }

    public function userBank(){
        return $this->hasMany('App\Models\UserBank','user_id','id');
    }

    public function userData(){
        return $this->hasMany('App\Models\UserData');
    }

    public function userDataDefault(){
        return $this->userData()->whereNotNull('is_default')->first();
    }

    public function userBrand(){
        return $this->hasMany(UserBrand::class, 'account_id', 'id');
    }

    public function accountBrand()
{
    return $this->hasManyThrough('App\Models\User', 'App\Models\UserBrand', 'user_id', 'id', 'id', 'account_id');
}

    public function listUserBrand(){
        $arr = [];
        foreach($this->userBrand as $key => $value) {

            $arr[] = $value->brand->name ??'-';
        }
        return implode(',',$arr);
    }

    public function userDestination(){
        return $this->hasMany('App\Models\UserDestination');
    }

    public function userDestinationDocument(){
        return $this->hasMany('App\Models\UserDestinationDocument');
    }

    public function getBillingAddress(){
        $arr = [];
        foreach($this->userData as $row){
            $country = $row->country()->exists() ? $row->country->name : '';
            $province = $row->province()->exists() ? $row->province->name : '';
            $city = $row->city()->exists() ? $row->city->name : '';
            $district = $row->district()->exists() ? $row->district->name : '';
            $arr[] = [
                'id'        => $row->id,
                'npwp'      => $row->npwp,
                'address'   => $row->address.' - '.$district.' - '.$city.' - '.$province.' - '.$country,
            ];
        }
        return $arr;
    }

    public function userDriver(){
        return $this->hasMany('App\Models\UserDriver');
    }

    public function defaultBank(){
        $bank = '';

        foreach(UserBank::where('user_id',$this->id)->where('is_default','1')->get() as $row){
            $bank = $row->bank.' A/N '.$row->name.' Rek. '.$row->no;
        }

        return $bank;
    }

    public function arrBanks(){
        $banks = [];

        foreach($this->userBank()->orderByDesc('is_default')->get() as $row){
            $banks[] = [
                'bank'      => $row->bank,
                'name'      => $row->name,
                'no'        => $row->no,
            ];
        }

        return $banks;
    }

    public function fundRequest(){
        return $this->hasMany('App\Models\FundRequest','account_id','id')->whereIn('status',['1','2','3','7']);
    }

    public function purchaseOrder(){
        return $this->hasMany('App\Models\PurchaseOrder','account_id','id')->whereIn('status',['2','3']);
    }

    public function goodReceipt(){
        return $this->hasMany('App\Models\GoodReceipt','account_id','id')->whereIn('status',['2','3']);
    }

    public function purchaseDownPayment(){
        return $this->hasMany('App\Models\PurchaseDownPayment','account_id','id')->whereIn('status',['2','3','7']);
    }

    public function purchaseInvoice(){
        return $this->hasMany('App\Models\PurchaseInvoice','account_id','id')->whereIn('status',['2','3','7']);
    }

    public function marketingOrderDownPayment(){
        return $this->hasMany('App\Models\MarketingOrderDownPayment','account_id','id')->whereIn('status',['2','3']);
    }

    public function marketingOrderInvoice(){
        return $this->hasMany('App\Models\MarketingOrderInvoice','account_id','id')->whereIn('status',['2','3']);
    }

    public function marketingOrderMemo(){
        return $this->hasMany('App\Models\MarketingOrderMemo','account_id','id')->whereIn('status',['2','3']);
    }

    public function marketingOrder(){
        return $this->hasMany('App\Models\MarketingOrder','account_id','id')->whereIn('status',['2','3']);
    }

    public function landedCost(){
        return $this->hasMany('App\Models\LandedCost','account_id','id')->whereIn('status',['2','3']);
    }

    public function hasDocument(){
        $has = false;
        if($this->fundRequest()->exists() || $this->purchaseOrder()->exists() || $this->goodReceipt()->exists() || $this->purchaseDownPayment()->exists() || $this->purchaseInvoice()->exists() || $this->marketingOrderDownPayment()->exists() || $this->marketingOrderInvoice()->exists() || $this->marketingOrderMemo()->exists() || $this->marketingOrderMemo()->exists() || $this->landedCost()->exists()){
            $has = true;
        }

        return $has;
    }

    public function subordinate(){
        return $this->hasMany('App\Models\User','manager_id','id');
    }

    public function getAllSubordinates()
    {
        $allSubordinates = [];
        $this->getAllSubordinatesRecursive($this, $allSubordinates);
        return $allSubordinates;
    }

    protected function getAllSubordinatesRecursive($user, &$allSubordinates)
    {
        $subordinates = $user->subordinate;
        foreach ($subordinates as $subordinate) {
            $allSubordinates[] = $subordinate;
            $this->getAllSubordinatesRecursive($subordinate, $allSubordinates);
        }
    }

    public function grandtotalUnsentMod(){
        $totalMod = 0;
        foreach($this->marketingOrder as $row){
            foreach($row->marketingOrderDetail as $rowdetail){
                foreach($rowdetail->marketingOrderDeliveryDetail()->whereHas('marketingOrderDelivery',function($query){
                    $query->where('status','2');
                })->get() as $rowmod){
                    $totalMod += $rowmod->getGrandtotal();
                }
            }
        }

        return $totalMod;
    }

    public function grandtotalUnsentModCredit(){
        $query = "
                    SELECT SUM(
            ROUND(
                (
                    CASE
                        WHEN modtdelivery.tax_id > 0 AND modtdelivery.is_include_tax = '1' AND DATE(mo.updated_at) < '2024-12-24'
                        THEN
                            (modeliverydetail.qty * modtdelivery.price_after_discount * modtdelivery.qty_conversion) / (1 + (modtdelivery.percent_tax / 100))
                        ELSE
                            modeliverydetail.qty * modtdelivery.price_after_discount * modtdelivery.qty_conversion
                    END
                )
                +
                CASE
                    WHEN modtdelivery.tax_id > 0 THEN
                        -- Use the same logic for total_case_1 here
                        (
                            CASE
                                WHEN modtdelivery.tax_id > 0 AND modtdelivery.is_include_tax = '1' AND DATE(mo.updated_at) < '2024-12-24'
                                THEN
                                    (modeliverydetail.qty * modtdelivery.price_after_discount * modtdelivery.qty_conversion) / (1 + (modtdelivery.percent_tax / 100))
                                ELSE
                                    modeliverydetail.qty * modtdelivery.price_after_discount * modtdelivery.qty_conversion
                            END
                        ) * (modtdelivery.percent_tax / 100)
                    ELSE
                        0
                END
                , 2) * ((100 - mo.percent_dp) / 100)
        ) AS totalMod
        FROM marketing_orders mo
        JOIN marketing_order_details modetail ON modetail.marketing_order_id = mo.id
        JOIN marketing_order_delivery_details modeliverydetail ON modeliverydetail.marketing_order_detail_id = modetail.id
        JOIN marketing_order_details modtdelivery ON modeliverydetail.marketing_order_detail_id = modtdelivery.id
        JOIN marketing_order_deliveries moddelivery ON moddelivery.id = modeliverydetail.marketing_order_delivery_id
        WHERE moddelivery.status = 2
        AND mo.account_id = :account_id;
        ";
        $totalMod = DB::select($query, ['account_id' => $this->id]);
        $totalModValue = $totalMod[0]->totalMod ?? 0;
        return $totalModValue;
    }

    public function grandtotalUnsentModDp(){
        $query = "
                    SELECT SUM(
            ROUND(
                (
                    CASE
                        WHEN modtdelivery.tax_id > 0 AND modtdelivery.is_include_tax = '1' AND DATE(mo.updated_at) < '2024-12-24'
                        THEN
                            (modeliverydetail.qty * modtdelivery.price_after_discount * modtdelivery.qty_conversion) / (1 + (modtdelivery.percent_tax / 100))
                        ELSE
                            modeliverydetail.qty * modtdelivery.price_after_discount * modtdelivery.qty_conversion
                    END
                )
                +
                CASE
                    WHEN modtdelivery.tax_id > 0 THEN
                        -- Use the same logic for total_case_1 here
                        (
                            CASE
                                WHEN modtdelivery.tax_id > 0 AND modtdelivery.is_include_tax = '1' AND DATE(mo.updated_at) < '2024-12-24'
                                THEN
                                    (modeliverydetail.qty * modtdelivery.price_after_discount * modtdelivery.qty_conversion) / (1 + (modtdelivery.percent_tax / 100))
                                ELSE
                                    modeliverydetail.qty * modtdelivery.price_after_discount * modtdelivery.qty_conversion
                            END
                        ) * (modtdelivery.percent_tax / 100)
                    ELSE
                        0
                END
                , 2) * ((100 - mo.percent_dp) / 100)
        ) AS totalMod
        FROM marketing_orders mo
        JOIN marketing_order_details modetail ON modetail.marketing_order_id = mo.id
        JOIN marketing_order_delivery_details modeliverydetail ON modeliverydetail.marketing_order_detail_id = modetail.id
        JOIN marketing_order_details modtdelivery ON modeliverydetail.marketing_order_detail_id = modtdelivery.id
        JOIN marketing_order_deliveries moddelivery ON moddelivery.id = modeliverydetail.marketing_order_delivery_id
        WHERE moddelivery.status = 2
        AND mo.account_id = :account_id
        AND mo.percent_dp > 0;
        ";
        $totalMod = DB::select($query, ['account_id' => $this->id]);
        $totalModValue = $totalMod[0]->totalMod ?? 0;
        return $totalModValue;
    }

    public function grandtotalUninvoiceDo(){
        $totalDo = 0;
        foreach($this->marketingOrder as $row){
            foreach($row->marketingOrderDetail as $rowdetail){
                foreach($rowdetail->marketingOrderDeliveryDetail()->whereHas('marketingOrderDeliveryProcessDetail',function($query){
                    $query->whereHas('marketingOrderDeliveryProcess',function($query){
                        $query->where('status','2');
                    });
                })->get() as $rowmod){
                    $totalDo += $rowmod->getGrandtotal();
                }
            }
        }

        return $totalDo;
    }

    public function grandtotalUninvoiceDoCredit(){
        $query = "
            SELECT
                SUM(modified_grandtotal) AS totalMod
            FROM (
                SELECT
                    ROUND(
                        (
                            CASE
                                WHEN modtdelivery.tax_id > 0 AND modtdelivery.is_include_tax = '1' AND DATE(mo.updated_at) < '2024-12-24'
                                THEN
                                    (modeliverydetail.qty * modtdelivery.price_after_discount * modtdelivery.qty_conversion) / (1 + (modtdelivery.percent_tax / 100))
                                ELSE
                                    modeliverydetail.qty * modtdelivery.price_after_discount * modtdelivery.qty_conversion
                            END
                        )
                        +
                        CASE
                            WHEN modtdelivery.tax_id > 0 THEN
                                (
                                    CASE
                                        WHEN modtdelivery.tax_id > 0 AND modtdelivery.is_include_tax = '1' AND DATE(mo.updated_at) < '2024-12-24'
                                        THEN
                                            (modeliverydetail.qty * modtdelivery.price_after_discount * modtdelivery.qty_conversion) / (1 + (modtdelivery.percent_tax / 100))
                                        ELSE
                                            modeliverydetail.qty * modtdelivery.price_after_discount * modtdelivery.qty_conversion
                                    END
                                ) * (modtdelivery.percent_tax / 100)
                            ELSE
                                0
                        END
                        , 2) * ((100 - mo.percent_dp) / 100) AS modified_grandtotal
                FROM marketing_orders mo
                JOIN marketing_order_details modetail ON modetail.marketing_order_id = mo.id
                JOIN marketing_order_delivery_details modeliverydetail ON modeliverydetail.marketing_order_detail_id = modetail.id
                JOIN marketing_order_details modtdelivery ON modeliverydetail.marketing_order_detail_id = modtdelivery.id
                JOIN marketing_order_deliveries moddelivery ON moddelivery.id = modeliverydetail.marketing_order_delivery_id
                JOIN marketing_order_delivery_process_details modeliveryprocessdetail ON modeliveryprocessdetail.marketing_order_delivery_detail_id = modeliverydetail.id
                JOIN marketing_order_delivery_processes modeliveryprocess ON modeliveryprocess.id = modeliveryprocessdetail.marketing_order_delivery_process_id
                WHERE modeliveryprocess.status = 2
                AND mo.account_id = :account_id
                GROUP BY
                    modetail.id,
                    modeliverydetail.qty,
                    modtdelivery.price_after_discount,
                    modtdelivery.qty_conversion,
                    modtdelivery.tax_id,
                    modtdelivery.is_include_tax,
                    modtdelivery.percent_tax,
                    mo.percent_dp,
                    mo.updated_at
            ) AS grouped_orders;
        ";
        $totalMod = DB::select($query, ['account_id' => $this->id]);
        $totalModValue = $totalMod[0]->totalMod ?? 0;
        return $totalModValue;
    }

    public function grandtotalUninvoiceDoDp(){
        $query = "
            SELECT
                SUM(modified_grandtotal) AS totalMod
            FROM (
                SELECT
                    ROUND(
                        (
                            CASE
                                WHEN modtdelivery.tax_id > 0 AND modtdelivery.is_include_tax = '1' AND DATE(mo.updated_at) < '2024-12-24'
                                THEN
                                    (modeliverydetail.qty * modtdelivery.price_after_discount * modtdelivery.qty_conversion) / (1 + (modtdelivery.percent_tax / 100))
                                ELSE
                                    modeliverydetail.qty * modtdelivery.price_after_discount * modtdelivery.qty_conversion
                            END
                        )
                        +
                        CASE
                            WHEN modtdelivery.tax_id > 0 THEN
                                (
                                    CASE
                                        WHEN modtdelivery.tax_id > 0 AND modtdelivery.is_include_tax = '1' AND DATE(mo.updated_at) < '2024-12-24'
                                        THEN
                                            (modeliverydetail.qty * modtdelivery.price_after_discount * modtdelivery.qty_conversion) / (1 + (modtdelivery.percent_tax / 100))
                                        ELSE
                                            modeliverydetail.qty * modtdelivery.price_after_discount * modtdelivery.qty_conversion
                                    END
                                ) * (modtdelivery.percent_tax / 100)
                            ELSE
                                0
                        END
                        , 2) * ((100 - mo.percent_dp) / 100) AS modified_grandtotal
                FROM marketing_orders mo
                JOIN marketing_order_details modetail ON modetail.marketing_order_id = mo.id
                JOIN marketing_order_delivery_details modeliverydetail ON modeliverydetail.marketing_order_detail_id = modetail.id
                JOIN marketing_order_details modtdelivery ON modeliverydetail.marketing_order_detail_id = modtdelivery.id
                JOIN marketing_order_deliveries moddelivery ON moddelivery.id = modeliverydetail.marketing_order_delivery_id
                JOIN marketing_order_delivery_process_details modeliveryprocessdetail ON modeliveryprocessdetail.marketing_order_delivery_detail_id = modeliverydetail.id
                JOIN marketing_order_delivery_processes modeliveryprocess ON modeliveryprocess.id = modeliveryprocessdetail.marketing_order_delivery_process_id
                WHERE modeliveryprocess.status = 2
                AND mo.account_id = :account_id
                AND mo.percent_dp > 0
                GROUP BY
                    modetail.id,
                    modeliverydetail.qty,
                    modtdelivery.price_after_discount,
                    modtdelivery.qty_conversion,
                    modtdelivery.tax_id,
                    modtdelivery.is_include_tax,
                    modtdelivery.percent_tax,
                    mo.percent_dp,
                    mo.updated_at
            ) AS grouped_orders;
        ";
        $totalMod = DB::select($query, ['account_id' => $this->id]);
        $totalModValue = $totalMod[0]->totalMod ?? 0;
        return $totalModValue;
    }

    public function userPlace(){
        return $this->hasMany('App\Models\UserPlace');
    }

    public function userPlaceArray(){
        $arr = [];
        foreach($this->userPlace as $row){
            $arr[] = $row->place_id;
        }
        return $arr;
    }

    public function userPlaceCodeArray(){
        $arr = [];
        foreach($this->userPlace as $row){
            $arr[] = $row->place->code;
        }
        return $arr;
    }

    public function userWarehouse(){
        return $this->hasMany('App\Models\UserWarehouse');
    }

    public function leaveQuotas(){

        return $this->hasMany('App\Models\EmployeeLeaveQuotas');
    }

    public function getQuotasUser($year){
        $return = $this->leaveQuotas()->where('start_date','like',$year."%")->first();

        return $return->paid_leave_quotas??0;
    }

    public function userWarehouseArray(){
        $arr = [];
        foreach($this->userWarehouse as $row){
            $arr[] = $row->warehouse_id;
        }
        return $arr;
    }

    public function registration(){
        return $this->belongsTo('App\Models\Registration','registration_id','id')->withTrashed();
    }

    public function userFile(){
        return $this->hasMany('App\Models\UserFile');
    }

    public function province(){
        return $this->belongsTo('App\Models\Region','province_id','id')->withTrashed();
    }

    public function group(){
        return $this->belongsTo('App\Models\Group','group_id','id')->withTrashed();
    }

    public function city(){
        return $this->belongsTo('App\Models\Region','city_id','id')->withTrashed();
    }

    public function district(){
        return $this->belongsTo('App\Models\Region','district_id','id')->withTrashed();
    }

    public function country(){
        return $this->belongsTo('App\Models\Country','country_id','id')->withTrashed();
    }

    public function brand(){
        return $this->belongsTo('App\Models\Brand','brand_id','id')->withTrashed();
    }

    public function company(){
        return $this->belongsTo('App\Models\Company','company_id','id')->withTrashed();
    }

    public function place(){
        return $this->belongsTo('App\Models\Place','place_id','id')->withTrashed();
    }

    public function manager(){
        return $this->belongsTo('App\Models\User','manager_id','id')->withTrashed();
    }

    public function menuUser(){
        return $this->hasMany('App\Models\MenuUser','user_id','id');
    }

    public function menuDistinct(){
        $arr = [];
        foreach($this->menuUser()->distinct('menu_id')->pluck('menu_id') as $menu){
            $arr[] = $menu;
        }
        return $arr;
    }

    public function position(){
        return $this->belongsTo('App\Models\Position','position_id','id')->withTrashed();
    }

    public function availablePurchaseOrder(){
        return $this->hasMany('App\Models\PurchaseOrder','account_id','id')->whereIn('status',['2','3']);
    }

    public function checkMenu($id,$type){
        $cek = MenuUser::where('menu_id', $id)->where('user_id', $this->id)->where('type',$type)->first();

        if($cek){
            return true;
        }else{
            return false;
        }
    }

    public function checkPlace($id){
        $cek = UserPlace::where('place_id', $id)->where('user_id', $this->id)->first();

        if($cek){
            return 'checked';
        }else{
            return '';
        }
    }

    public function checkWarehouse($id){
        $cek = UserWarehouse::where('warehouse_id', $id)->where('user_id', $this->id)->first();

        if($cek){
            return 'checked';
        }else{
            return '';
        }
    }
    public function workOrder(){
        return $this->hasMany('App\Models\WorkOrder')->whereIn('status',['2','3']);
    }

    public function cekMinMaxPostDate($url){
        $cek = UserDateUser::where('user_id',$this->id)
                ->whereHas('userDate',function($query)use($url){
                    $query->whereHas('userDateMenu',function($query)use($url){
                        $query->whereHas('menu',function($query)use($url){
                            $query->where('url',$url);
                        });
                    })
                    ->where('status','1');
                })
                ->first();

        return $cek ? $cek : '';
    }

    public function createApiToken()
    {
        $token = Str::random(64);
        $this->api_token = $token;
        $this->save();
        return $token;
    }

    public function statusRaw(){

        $status = match ($this->status) {
            '1' => 'Aktif',
            '2' => 'Tidak Aktif',

            default => 'Invalid',
        };

        return $status;
    }

    public function mitraCustomer(){
        return $this->hasOne('App\Models\MitraCustomer','user_id','id');
    }
}
