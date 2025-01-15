<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

/*
    tabel mitra_customer_billings nanti akan masuk ke tabel internal user_datas dan user_destination_documents
*/

class MitraCustomerBilling extends Model{
    use HasFactory, Notifiable;

    protected $table      = 'mitra_customer_billings';
    protected $primaryKey = 'id';
    protected $fillable   = [
        'mitra_customer_id',
        'name',
        'notes',
        'npwp',
        'address',
        'country_id',
        'province_id',
        'city_id',
        'district_id',
    ];

    //default value
    protected $attributes = [
        'country_id' => 103,     // Indonesia
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
}
