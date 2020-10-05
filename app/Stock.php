<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{

    public function product()
    {
        return $this->belongsTo('\App\Product', 'product_id', 'id');
    }

    protected $fillable = ['product_id', 'quantity', 'unit_id', 'price'];
}
