<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class MitraComplaintSales extends Model{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table      = 'mitra_complaint_sales';
    protected $primaryKey = 'id';
    protected $dates      = ['deleted_at'];

    protected $fillable = [
        'delivery_order_code',  //Kode SJ, 1 SJ 1 komplain
        'delivery_order_id',    //ID SJ
        'customer_id',          //ID customer / BP, bukan mitracustomer
        'qty_delivered',        //Qty terkirim, free text
        'sales_name',           //Nama sales Tirta, free text
        'document',             //Foto
        'note',                 //Keterangan
        'complaint_sales_id',   //ID Complaint Sales Internal
        'mitra_id',             //ID Mitra
        'status',               //1 Menunggu, 2 Proses, 3 Selesai
    ];

    public function deliveryOrder(){
        return $this->belongsTo('App\Models\MarketingOrderDeliveryProcess', 'delivery_order_id', 'id')->withTrashed();
    }

    public function customer(){
        return $this->belongsTo('App\Models\User', 'customer_id', 'id')->withTrashed();
    }

    public function complaintSales(){
        return $this->belongsTo('App\Models\ComplaintSales', 'complaint_sales_id', 'id')->withTrashed();
    }

    public function mitra(){
        return $this->belongsTo('App\Models\User', 'mitra_id', 'id')->withTrashed();
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
}
