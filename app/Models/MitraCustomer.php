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
        'type',             // PT, CV, Badan Usaha, Perorangan
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
        'status_approval',  // Status Approval: 1 Approved, 2 Pending Create, 3 Pending Update, 4 Rejected
        'user_id',          // link ID BP di table user (employee_no)
        'mitra_id',         // ID Broker (employee_no) dari table user
        'status',           // Status: 2 Non Aktif, 1 Aktif
    ];

    public function province(){
        return $this->belongsTo('App\Models\Region','province_id','id')->withTrashed();
    }

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }

    public function city(){
        return $this->belongsTo('App\Models\Region','city_id','id')->withTrashed();
    }

    public function district(){
        return $this->belongsTo('App\Models\Region','district_id','id')->withTrashed();
    }

    public function deliveryAddress(){
        return $this->hasMany('App\Models\MitraCustomerDelivery', 'mitra_customer_id', 'id');
    }

    public function billingAddress(){
        return $this->hasMany('App\Models\MitraCustomerBilling', 'mitra_customer_id', 'id');
    }

    public function statusApprovalRaw(){
        $status_approval = match ($this->status_approval) {
            '1' => 'Approved',
            '2' => 'Pending Insert',
            '3' => 'Pending Update',
            '4' => 'Rejected',
            default => 'Invalid',
        };
        return $status_approval;
    }

    public function statusRaw(){
        $status = match ($this->status) {
            '1' => 'Active',
            '2' => 'Not Active',
            default => 'Invalid',
        };
        return $status;
    }
}
