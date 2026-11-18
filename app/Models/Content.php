<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $guarded = [];

    public function contentType()
    {
        return $this->belongsTo(ContentType::class, 'content_type_id');
    }

    public function contentCategory()
    {
        return $this->belongsTo(ContentCategory::class, 'content_category_id');
    }
}
