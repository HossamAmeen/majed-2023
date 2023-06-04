<?php

namespace App\Models;
    use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    use SoftDeletes;
    protected $dates=['deleted_at'];

    protected $fillable = [
        'product_price','price' , 'quantity','date', 'status' ,'product_name',
        'discount', 'user_id','product_id','bill_id'
    ];

    public function user()
    {
    	return $this->belongsTo(User::class);
    }
    public function product()
    {
    	return $this->belongsTo(Product::class)->withTrashed();
    }
    // public function productWithDelted()
    // {
    //     return $this->belongsTo(Product::class)->withTrashed();
    // }
    public function bill()
    {
    	return $this->belongsTo(Bill::class);
    }
}
