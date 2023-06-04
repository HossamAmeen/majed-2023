<?php

namespace App\Models;

use App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];

    protected $fillable = [
        'name' , 'purchasing_price','selling_price', 'quantity' ,
        'total_price', 'code' , 'discount' , 'user_id','category_id'
    ];
    public function category()
    {
    	return $this->belongsTo(category::class);

    }
    public function user()
    {
    	return $this->belongsTo(User::class);
    }
    public function order()
    {
    	return $this->belongsToMany(Order::class);
    }
    public function bill()
    {
    	return $this->belongsToMany(Bill::class);
    }
}
