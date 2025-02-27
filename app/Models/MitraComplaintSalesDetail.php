<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class MitraComplaintSalesDetail extends Model{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table      = 'mitra_complaint_sale_details';
    protected $primaryKey = 'id';
    protected $dates      = ['deleted_at'];

    protected $fillable = [
        'mitra_complaint_sales_id',
        'item_id',
        'batch_code',
        'note',
        'qty_color_mistake',
        'qty_motif_mistake',
        'qty_size_mistake',
        'qty_broken',
        'qty_mistake',
    ];

    public function mitraComplaintSales(){
        return $this->belongsTo('App\Models\MitraComplaintSales', 'mitra_complaint_sales_id', 'id')->withTrashed();
    }

    public function item(){
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }
}
