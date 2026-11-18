<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentCategory extends Model
{
    protected $guarded = [];

    public function contentType()
    {
        return $this->belongsTo(ContentType::class, 'content_type_id');
    }

    public function contents()
    {
        return $this->hasMany(Content::class, 'content_category_id');
    }
}
