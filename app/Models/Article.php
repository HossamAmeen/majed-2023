<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'description', 'en_title', 'en_description', 'image', 'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getEnSubDesAttribute()
    {
        return substr(strip_tags(html_entity_decode((string) $this->en_description)), 0, 150);
    }

    public function getArSubDesAttribute()
    {
        return substr(strip_tags(html_entity_decode((string) $this->description)), 0, 100);
    }
}
