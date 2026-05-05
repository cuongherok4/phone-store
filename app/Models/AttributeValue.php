<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    public $timestamps = false;
    protected $fillable = ['attribute_id', 'value', 'numeric_value', 'color_hex', 'sort_order'];

    public function attribute() { return $this->belongsTo(Attribute::class); }
}