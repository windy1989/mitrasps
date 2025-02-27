<?php

namespace App\Models;

use App\Helpers\CustomHelper;
use App\Helpers\WaBlas;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MarketingOrderDelivery extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table      = 'marketing_order_deliveries';
    protected $primaryKey = 'id';
    protected $dates      = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'user_update_id',
        'update_time',
        'company_id',
        'account_id',
        'customer_id',
        'marketing_order_delivery_id',
        'post_date',
        'delivery_date',
        'destination_address',
        'city_id',
        'district_id',
        'transportation_id',
        'cost_delivery_type',
        'type_delivery',
        'so_type',
        'top_internal',
        'note_internal',
        'note_external',
        'status',
        'send_status',
        'stage_status',
        'grandtotal',
        'void_id',
        'void_note',
        'void_date',
        'delete_id',
        'delete_note',
        'done_id',
        'done_date',
        'done_note',
    ];

    public function customer(){
        return $this->belongsTo('App\Models\User', 'customer_id', 'id')->withTrashed();
    }
}
