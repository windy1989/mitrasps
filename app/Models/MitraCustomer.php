<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class MitraCustomer extends Model{
    use HasFactory, SoftDeletes, Notifiable;
    
    protected $table      = 'mitra_customers';
    protected $primaryKey = 'id';
    protected $dates      = ['deleted_at'];
    protected $fillable   = [
        'code',             // code customer dari mitra
        'name',             // nama customer dari mitra
        'type',
        'branch_code',      // keterangan cabang mitra
        'phone',
        'email',
        'address',
        'province_id',
        'city_id',
        'district_id',
        'id_card',
        'pic_name',
        'pic_address',
        'limit_credit',
        'top',
        'top_internal',
        'status_approval',  // approved & accepted / rejected
        'user_id',          // link ID BP di table user (employee_no)
        'mitra_id',         // ID Broker (employee_no) dari table user
        'status',
    ];

    public function province(){
        return $this->belongsTo('App\Models\Region','province_id','id')->withTrashed();
    }

    public function city(){
        return $this->belongsTo('App\Models\Region','city_id','id')->withTrashed();
    }

    public function district(){
        return $this->belongsTo('App\Models\Region','district_id','id')->withTrashed();
    }

    public function delivery_address(){
        return $this->hasMany('App\Models\MitraCustomerDelivery', 'mitra_customer_id', 'id');
    }

    public function billing_address(){
        return $this->hasMany('App\Models\MitraCustomerBilling', 'mitra_customer_id', 'id');
    }
}
