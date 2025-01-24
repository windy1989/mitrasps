<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ItemUnit extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'item_units';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'item_id',
        'unit_id',
        'conversion',
        'is_sell_unit',
        'is_buy_unit',
        'is_default',
    ];

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function unit(){
        return $this->belongsTo('App\Models\Unit','unit_id','id')->withTrashed();
    }

    public function sellUnitRaw(){
        $hide = match ($this->is_sell_unit) {
            '1' => 'Ya',
            default => 'Tidak',
          };
        return $hide;
    }

    public function buyUnitRaw(){
        $hide = match ($this->is_buy_unit) {
            '1' => 'Ya',
            default => 'Tidak',
          };
        return $hide;
    }
    public function defaultRaw(){
        $hide = match ($this->is_default) {
            '1' => 'V',
            default => 'Tidak',
          };
        return $hide;
    }
}
