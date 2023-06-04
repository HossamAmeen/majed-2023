<?php

namespace App\Models;
    use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class BillsProduct extends Model
{

    use SoftDeletes;
    protected $dates=['deleted_at'];
    public function product()
    {
        return $this->belongsTo(Product::class , 'product_id');
    }
}
