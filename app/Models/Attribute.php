<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'code'];

    public function values() { return $this->hasMany(AttributeValue::class)->orderBy('sort_order'); }
}