<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MitraMarketingOrderDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'mitra_marketing_order_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'mitra_marketing_order_id',
        'item_id',
        'qty',
        'price',
        'percent_tax',
        'final_price',
        'total',
        'tax',
        'grandtotal',
        'note',
    ];

    public function mitraMarketingOrder()
    {
        return $this->belongsTo('App\Models\MitraMarketingOrder', 'mitra_marketing_order_id', 'id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }
}
