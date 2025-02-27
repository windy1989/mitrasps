<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MarketingOrderDeliveryProcessDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table      = 'marketing_order_delivery_process_details';
    protected $primaryKey = 'id';
    protected $dates      = ['deleted_at'];
    protected $fillable = [
        'marketing_order_delivery_process_id',
        'marketing_order_delivery_detail_id',
        'item_stock_id',
        'qty',
        'total',
    ];
}
