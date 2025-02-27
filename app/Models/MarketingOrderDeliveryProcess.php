<?php

namespace App\Models;

use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MarketingOrderDeliveryProcess extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table      = 'marketing_order_delivery_processes';
    protected $primaryKey = 'id';
    protected $dates      = ['deleted_at'];
    protected $fillable   = [
        'user_id',
        'code',
        'company_id',
        'account_id',
        'marketing_order_delivery_id',
        'post_date',
        'receive_date',
        'return_date',
        'user_driver_id',
        'driver_name',
        'driver_hp',
        'vehicle_name',
        'vehicle_no',
        'no_container',
        'seal_no',
        'weight_netto',
        'note_internal',
        'note_external',
        'status',
        'status_tracking',
        'document',
        'total',
        'tax',
        'rounding',
        'grandtotal',
        'void_id',
        'void_note',
        'void_date',
        'delete_id',
        'delete_note',
        'done_id',
        'done_date',
        'done_note',
        'scan_barcode',
        'revision_counter',
    ];

    public function marketingOrderDelivery(){
        return $this->belongsTo('App\Models\MarketingOrderDelivery', 'marketing_order_delivery_id', 'id')->withTrashed();
    }
}
