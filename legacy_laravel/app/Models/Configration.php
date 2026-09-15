<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configration extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'en_title', 'email', 'home_description', 'en_home_description', 'address',
        'description', 'en_description', 'descriptionPoint', 'phone', 'phone2', 'video',
        'video2', 'youtube', 'facebook', 'twitter', 'instagram',
    ];
}
