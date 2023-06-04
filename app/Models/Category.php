<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];

    protected $fillable = [
        'name' ,'user_id'
    ];


    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    public function product()
    {
    	return $this->hasmany(Product::class);
    }
}

