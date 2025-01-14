<?php

namespace App\Models;

use App\Helpers\CustomHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MitraMarketingOrder extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'mitra_marketing_orders';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'account_id',
        'type',
        'post_date',
        'valid_date',
        'document_no',
        'branch_code',
        'type_delivery',
        'delivery_date',
        'delivery_schedule',
        'delivery_address',
        'delivery_province_code',
        'delivery_city_code',
        'delivery_district_code',
        'payment_type',
        'dp_type',
        'percent_dp',
        'note',
        'total',
        'tax',
        'grandtotal',
        'status',
        'void_id',
        'void_note',
        'void_date',
        'delete_id',
        'delete_note',
        'done_id',
        'done_date',
        'done_note',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function voidUser()
    {
        return $this->belongsTo('App\Models\User', 'void_id', 'id')->withTrashed();
    }

    public function doneUser()
    {
        return $this->belongsTo('App\Models\User', 'done_id', 'id')->withTrashed();
    }

    public function deleteUser()
    {
        return $this->belongsTo('App\Models\User', 'delete_id', 'id')->withTrashed();
    }

    public function account(){
        return $this->belongsTo('App\Models\User','account_id','id')->withTrashed();
    }

    public function deliveryProvince(){
        return $this->belongsTo('App\Models\Region','delivery_province_code','code')->withTrashed();
    }

    public function deliveryCity(){
        return $this->belongsTo('App\Models\Region','delivery_city_code','code')->withTrashed();
    }

    public function deliveryDistrict(){
        return $this->belongsTo('App\Models\Region','delivery_district_code','code')->withTrashed();
    }

    public function paymentType(){
        $type = match ($this->payment_type) {
            '1' => 'DP',
            '2' => 'Credit',
            default => 'Invalid',
        };

        return $type;
    }

    public function dpType(){
        $dp_type = match ($this->dp_type) {
            '1' => 'Proporsional',
            '2' => 'FIFO',
            default => '-',
        };

        return $dp_type;
    }

    public function deliverySchedule(){
        $delivery_schedule = match ($this->delivery_schedule) {
            '1' => 'Segera',
            '2' => 'Terjadwal',
            '3' => 'Belum Terjadwal',
            default => 'Invalid',
        };

        return $delivery_schedule;
    }

    public function type(){
        $type = match ($this->type) {
            '1' => 'Proyek',
            '2' => 'Retail',
            '3' => 'Khusus',
            '4' => 'Sample',
            default => 'Invalid',
        };

        return $type;
    }

    public function deliveryType(){
        $type = match ($this->type_delivery) {
            '1' => 'Loco',
            '2' => 'Franco',
          default => 'Invalid',
        };

        return $type;
    }

    public function mitraMarketingOrderDetail()
    {
        return $this->hasMany('App\Models\MitraMarketingOrderDetail');
    }

    public function used(){
        return $this->hasOne('App\Models\UsedData','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function marketingOrder(){
        return $this->hasOne('App\Models\MarketingOrder','document_no','document_no')->whereIn('status',['2','3']);
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="amber medium-small white-text padding-3">Menunggu</span>',
          '2' => '<span class="cyan medium-small white-text padding-3">Proses</span>',
          '3' => '<span class="green medium-small white-text padding-3">Selesai</span>',
          '4' => '<span class="red medium-small white-text padding-3">Ditolak</span>',
          '5' => '<span class="red darken-4 medium-small white-text padding-3">Ditutup</span>',
          '6' => '<span class="yellow darken-4 medium-small white-text padding-3">Revisi</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public function statusRaw(){
        $status = match ($this->status) {
            '1' => 'Menunggu',
            '2' => 'Proses',
            '3' => 'Selesai',
            '4' => 'Ditolak',
            '5' => 'Ditutup',
            '6' => 'Direvisi',
            default => 'Invalid',
        };

        return $status;
    }

    public static function generateCode($prefix)
    {
        $cek = substr($prefix,0,7);
        $query = MitraMarketingOrder::selectRaw('RIGHT(code, 8) as code')
            ->whereRaw("code LIKE '$cek%'")
            ->withTrashed()
            ->orderByDesc('code')
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '00000001';
        }

        $no = str_pad($code, 8, 0, STR_PAD_LEFT);

        return substr($prefix,0,9).'-'.$no;
    }

    public function approval(){
        $source = ApprovalSource::where('lookable_type',$this->table)->where('lookable_id',$this->id)->whereHas('approvalMatrix')->get();
        if($source){
            return $source;
        }else{
            return '';
        }
    }

    public function hasDetailMatrix(){
        $ada = false;
        if($this->approval()){
            foreach($this->approval() as $row){
                if($row->approvalMatrix()->exists()){
                    $ada = true;
                }
            }
        }

        return $ada;
    }

    public function hasChildDocument(){
        $hasRelation = false;

        if($this->marketingOrder()->exists()){
            $hasRelation = true;
        }

        return $hasRelation;
    }

    public function printCounter()
    {
        return $this->hasMany('App\Models\PrintCounter','lookable_id','id')->where('lookable_type',$this->table);
    }

    public function isOpenPeriod(){
        $monthYear = substr($this->post_date, 0, 7); // '2023-02'

        // Query the LockPeriod model
        $see = LockPeriod::where('month', $monthYear)
                        ->whereIn('status_closing', ['2','3'])
                        ->get();
       
        if(count($see)>0){
            return true;
        }else{
            return false;
        }
    }
}
