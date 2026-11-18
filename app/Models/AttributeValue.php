<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    protected $fillable = ['attribute_type_id', 'value', 'color_code', 'sort_order'];

    public function attributeType()
    {
        return $this->belongsTo(AttributeType::class);
    }

    public function variants()
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_attribute');
    }
}
