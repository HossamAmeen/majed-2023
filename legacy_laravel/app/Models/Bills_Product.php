<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bills_Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_price', 'selling_price', 'discount', 'quantity', 'bill_id', 'product_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
