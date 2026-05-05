<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'variant_id', 'warehouse_id', 'supplier_id', 'change_type',
        'quantity_change', 'quantity_before', 'quantity_after',
        'reference_type', 'reference_id', 'note', 'created_by', 'import_price',
    ];

    protected $casts = ['created_at' => 'datetime'];

    public function variant()   { return $this->belongsTo(ProductVariant::class, 'variant_id'); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function supplier()  { return $this->belongsTo(Supplier::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
}