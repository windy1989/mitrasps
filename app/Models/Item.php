<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;

class Item extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'items';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name',
        'other_name',
        'item_group_id',
        'uom_unit',
        'buy_unit',
        'buy_convert',
        'sell_unit',
        'sell_convert',
        'pallet_unit',
        'pallet_convert',
        'production_unit',
        'production_convert',
        'tolerance_gr',
        'is_inventory_item',
        'is_sales_item',
        'is_purchase_item',
        'is_service',
        'is_production',
        'note',
        'min_stock',
        'max_stock',
        'status',
        'is_quality_check',
        'is_hide_supplier',
        'is_reject',
        'type_id',
        'size_id',
        'variety_id',
        'pattern_id',
        'pallet_id',
        'grade_id',
        'brand_id',
        'bom_calculator_id',
        'print_name'
    ];

    public function itemGroup(){
        return $this->belongsTo('App\Models\ItemGroup', 'item_group_id', 'id')->withTrashed();
    }

    public function bomCalculator(){
        return $this->belongsTo('App\Models\BomCalculator', 'bom_calculator_id', 'id')->withTrashed();
    }

    public function type(){
        return $this->belongsTo('App\Models\Type', 'type_id', 'id')->withTrashed();
    }

    public function size(){
        return $this->belongsTo('App\Models\Size', 'size_id', 'id')->withTrashed();
    }

    public function variety(){
        return $this->belongsTo('App\Models\Variety', 'variety_id', 'id')->withTrashed();
    }

    public function itemFgPicture(){
        return $this->hasOne('App\Models\itemFGPicture','item_id','id');
    }

    public function pattern(){
        return $this->belongsTo('App\Models\Pattern', 'pattern_id', 'id')->withTrashed();
    }

    public function pallet(){
        return $this->belongsTo('App\Models\Pallet', 'pallet_id', 'id')->withTrashed();
    }

    public function grade(){
        return $this->belongsTo('App\Models\Grade', 'grade_id', 'id')->withTrashed();
    }

    public function brand(){
        return $this->belongsTo('App\Models\Brand', 'brand_id', 'id')->withTrashed();
    }

    public function warehouses(){
        $arr = [];

        foreach($this->itemGroup->itemGroupWarehouse as $row){
            $arr[] = $row->warehouse->name;
        }

        return implode(', ',$arr);
    }

    public function arrWarehouse(){
        $arr = [];

        foreach($this->itemGroup->itemGroupWarehouse as $row){
            $arr[] = $row->warehouse_id;
        }

        return $arr;
    }

    public function warehouseList(){
        $arr = [];

        foreach($this->itemGroup->itemGroupWarehouse as $row){
            $arr[] = [
                'id'    => $row->warehouse_id,
                'code'  => $row->warehouse->code,
                'name'  => $row->warehouse->name,
            ];
        }

        return $arr;
    }

    public function uomUnit(){
        return $this->belongsTo('App\Models\Unit', 'uom_unit', 'id')->withTrashed();
    }

    public function buyUnit(){
        return $this->belongsTo('App\Models\Unit', 'buy_unit', 'id')->withTrashed();
    }

    public function palletUnit(){
        return $this->belongsTo('App\Models\Pallet', 'pallet_unit', 'id')->withTrashed();
    }

    public function sellUnit(){
        /* return $this->belongsTo('App\Models\Unit', 'sell_unit', 'id')->withTrashed(); */
        $itemUnit = $this->itemUnit()->whereNotNull('is_default')->whereNotNull('is_sell_unit')->first();

        $unit = '';
        if($itemUnit){
            $unit = $itemUnit->unit->code;
        }
        return $unit;
    }

    public function itemUnitDefault(){
        /* return $this->belongsTo('App\Models\Unit', 'sell_unit', 'id')->withTrashed(); */
        $itemUnit = $this->itemUnit()->whereNotNull('is_default')->first();


        return $itemUnit;
    }

    public function itemUnitSellId(){
        /* return $this->belongsTo('App\Models\Unit', 'sell_unit', 'id')->withTrashed(); */
        $itemUnit = $this->itemUnit()->whereNotNull('is_default')->whereNotNull('is_sell_unit')->first();
        $unit = '';
        if($itemUnit){
            $unit = $itemUnit->id;
        }
        return $unit;
    }

    public function sellConversion(){
        /* return $this->belongsTo('App\Models\Unit', 'sell_unit', 'id')->withTrashed(); */
        $itemUnit = $this->itemUnit()->whereNotNull('is_default')->whereNotNull('is_sell_unit')->first();
        $unit = 1;
        if($itemUnit){
            $unit = $itemUnit->conversion;
        }
        return $unit;
    }

    public function productionUnit(){
        return $this->belongsTo('App\Models\Unit', 'production_unit', 'id')->withTrashed();
    }

    public function warehouse(){
        $warehouse = $this->itemGroup->itemGroupWarehouse()->first();
        return $warehouse->warehouse_id;
    }

    public function warehouseName(){
        $warehouse = $this->itemGroup->itemGroupWarehouse()->first();
        return $warehouse->warehouse->name;
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public function itemUnit()
    {
        return $this->hasMany('App\Models\ItemUnit','item_id','id');
    }
}
