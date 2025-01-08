<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'regions';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name',
        'country_id',
        'sale_area',
        'category_region',
    ];

    public function saleArea(){
        $sale_area = match ($this->sale_area) {
            '1' => 'SUMATERA',
            '2' => 'DKI JAKARTA JABAR',
            '3' => 'BALI NUSRA',
            '4' => 'JAWA TENGAH',
            '5' => 'JAWA TIMUR',
            '6' => 'KALIMANTAN',
            '7' => 'SULAWESI',
            '8' => 'MALUKU PAPUA',
            default => '-',
          };
  
          return $sale_area;
    }

    public function getDistrict(){
        $arr = [];
        $data = Region::where('code','like',"$this->code%")->whereRaw("CHAR_LENGTH(code) = 8")->get();
        foreach($data as $row){
            $arr[] = [
                'id'            => $row->id,
                'code'          => $row->code,
                'name'          => $row->name,
                'subdistrict'   => $row->getSubdistrict(),
            ];
        }
        return $arr;
    }

    public function getSubdistrict(){
        $arr = [];
        $data = Region::where('code','like',"$this->code%")->whereRaw("CHAR_LENGTH(code) = 13")->get();
        foreach($data as $row){
            $arr[] = [
                'id'    => $row->id,
                'code'  => $row->code,
                'name'  => $row->name,
            ];
        }
        return $arr;
    }

    public function getCity(){
        $arr = [];
        $data = Region::where('code','like',"$this->code%")->whereRaw("CHAR_LENGTH(code) = 5")->get();
        foreach($data as $row){
            $arr[] = [
                'id'            => $row->id,
                'code'          => $row->code,
                'name'          => $row->name,
                'district'      => $row->getDistrict(),
                'subdistrict'   => $row->getSubdistrict(),
            ];
        }
        return $arr;
    }

    public function getCityOnly(){
        $arr = [];
        $data = Region::where('code','like',"$this->code%")->whereRaw("CHAR_LENGTH(code) = 5")->get();
        foreach($data as $row){
            $arr[] = [
                'id'            => $row->id,
                'code'          => $row->code,
                'name'          => $row->name,
            ];
        }
        return $arr;
    }

    public function parentRegion(){
        $arr = explode('.', $this->code);
        $count = count($arr);

        $text = '-';

        if($count == 2){
            $text = Region::where('code', $arr[0])->first()->name;
        }elseif($count == 3){
            $text = Region::where('code', $arr[0])->first()->name.' - '.Region::where('code', $arr[0].'.'.$arr[1])->first()->name;
        }elseif($count == 4){
            $text = Region::where('code', $arr[0])->first()->name.' - '.Region::where('code', $arr[0].'.'.$arr[1])->first()->name.' - '.Region::where('code', $arr[0].'.'.$arr[1].'.'.$arr[2])->first()->name;
        }

        return $text;
    }

    public function getProvince(){
        $arr = explode('.', $this->code);

        $data = Region::where('code', $arr[0])->first();
        $x=$data->name;
        return $x;
    }

    public function city(){
        $arr = explode('.', $this->code);

        $data = Region::where('code', $arr[0].'.'. $arr[1])->first();
        $x=$data->name;
        return $x;
    }
    

    public function country()
    {
        return $this->belongsTo('App\Models\Country', 'country_id', 'id')->withTrashed();
    }

    public function type(){
        $category_region = match ($this->category_region) {
          '1' => 'DALAM PULAU JAWA',
          '2' => 'LUAR PULAU JAWA',
          default => '-',
        };

        return $category_region;
    }
}
