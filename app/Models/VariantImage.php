<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariantImage extends Model
{
    public $timestamps = false;
    protected $fillable = ['variant_id', 'image_url', 'is_primary', 'sort_order'];

    protected $casts = ['is_primary' => 'boolean'];

    public function variant() { return $this->belongsTo(ProductVariant::class, 'variant_id'); }
}