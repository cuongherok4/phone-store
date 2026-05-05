<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryLog;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryService
{
    /**
     * Lấy tổng tồn kho của 1 variant trên tất cả các kho
     */
    public function getStock(int $variantId): int
    {
        return Inventory::where('variant_id', $variantId)->sum('quantity');
    }

    /**
     * Trừ tồn kho khi có đơn hàng (ưu tiên kho có nhiều hàng nhất)
     */
    public function deduct(int $variantId, int $qty, int $orderId): void
    {
        if ($qty <= 0) return;

        DB::transaction(function () use ($variantId, $qty, $orderId) {
            $totalStock = $this->getStock($variantId);
            if ($totalStock < $qty) {
                throw new Exception("Không đủ số lượng tồn kho để trừ.");
            }

            // Lấy các kho đang có hàng của variant này, ưu tiên kho nhiều hàng nhất
            $inventories = Inventory::where('variant_id', $variantId)
                ->where('quantity', '>', 0)
                ->orderBy('quantity', 'desc')
                ->lockForUpdate()
                ->get();

            $remainingQtyToDeduct = $qty;

            foreach ($inventories as $inventory) {
                if ($remainingQtyToDeduct <= 0) break;

                $qtyToDeductFromThisWarehouse = min($inventory->quantity, $remainingQtyToDeduct);
                
                $quantityBefore = $inventory->quantity;
                $newQuantity = $inventory->quantity - $qtyToDeductFromThisWarehouse;
                
                Inventory::where('variant_id', $variantId)
                    ->where('warehouse_id', $inventory->warehouse_id)
                    ->update(['quantity' => $newQuantity]);

                InventoryLog::create([
                    'variant_id' => $variantId,
                    'warehouse_id' => $inventory->warehouse_id,
                    'change_type' => 'EXPORT',
                    'quantity_change' => -$qtyToDeductFromThisWarehouse,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $inventory->quantity,
                    'reference_type' => 'order',
                    'reference_id' => $orderId,
                    'note' => "Xuất kho cho đơn hàng #{$orderId}",
                    'created_by' => auth()->id() ?? 1 // Fallback cho hệ thống nếu không auth
                ]);

                $remainingQtyToDeduct -= $qtyToDeductFromThisWarehouse;
            }
        });
    }

    /**
     * Nhập thêm hàng vào một kho
     */
    public function import(int $variantId, int $warehouseId, int $qty, string $note, int $adminId, int $supplierId = null, float $importPrice = null): void
    {
        if ($qty <= 0) {
            throw new Exception("Số lượng nhập phải lớn hơn 0");
        }

        DB::transaction(function () use ($variantId, $warehouseId, $qty, $note, $adminId, $supplierId, $importPrice) {
            $inventory = Inventory::where('variant_id', $variantId)
                ->where('warehouse_id', $warehouseId)
                ->first();

            $quantityBefore = $inventory ? $inventory->quantity : 0;
            $newQuantity = $quantityBefore + $qty;

            if ($inventory) {
                Inventory::where('variant_id', $variantId)
                    ->where('warehouse_id', $warehouseId)
                    ->update(['quantity' => $newQuantity]);
            } else {
                Inventory::insert([
                    'variant_id' => $variantId,
                    'warehouse_id' => $warehouseId,
                    'quantity' => $newQuantity
                ]);
            }

            InventoryLog::create([
                'variant_id' => $variantId,
                'warehouse_id' => $warehouseId,
                'supplier_id' => $supplierId,
                'change_type' => 'IMPORT',
                'quantity_change' => $qty,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $newQuantity,
                'note' => $note,
                'created_by' => $adminId,
                'import_price' => $importPrice,
            ]);
        });
    }

    /**
     * Điều chỉnh số lượng tồn kho (set quantity)
     */
    public function adjust(int $variantId, int $warehouseId, int $newQty, string $note, int $adminId): void
    {
        if ($newQty < 0) {
            throw new Exception("Số lượng tồn kho không thể âm");
        }

        DB::transaction(function () use ($variantId, $warehouseId, $newQty, $note, $adminId) {
            $inventory = Inventory::where('variant_id', $variantId)
                ->where('warehouse_id', $warehouseId)
                ->first();

            $quantityBefore = $inventory ? $inventory->quantity : 0;
            if ($quantityBefore == $newQty && $inventory) return; // Không có thay đổi

            if ($inventory) {
                Inventory::where('variant_id', $variantId)
                    ->where('warehouse_id', $warehouseId)
                    ->update(['quantity' => $newQty]);
            } else {
                Inventory::insert([
                    'variant_id' => $variantId,
                    'warehouse_id' => $warehouseId,
                    'quantity' => $newQty
                ]);
            }

            InventoryLog::create([
                'variant_id' => $variantId,
                'warehouse_id' => $warehouseId,
                'change_type' => 'ADJUST',
                'quantity_change' => $newQty - $quantityBefore,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $newQty,
                'note' => $note,
                'created_by' => $adminId,
            ]);
        });
    }

    /**
     * Hoàn lại tồn kho khi đơn hàng bị huỷ
     */
    public function restore(int $variantId, int $qty, int $orderId): void
    {
        if ($qty <= 0) return;

        DB::transaction(function () use ($variantId, $qty, $orderId) {
            // Lấy kho mặc định hoặc kho đầu tiên để hoàn hàng vào
            $inventory = Inventory::where('variant_id', $variantId)->first();
            
            // Nếu variant này chưa từng có trong kho nào, lấy kho ID = 1 làm mặc định
            $warehouseId = $inventory ? $inventory->warehouse_id : 1;

            $quantityBefore = $inventory ? $inventory->quantity : 0;
            $newQuantity = $quantityBefore + $qty;

            if ($inventory) {
                Inventory::where('variant_id', $variantId)
                    ->where('warehouse_id', $warehouseId)
                    ->update(['quantity' => $newQuantity]);
            } else {
                Inventory::insert([
                    'variant_id' => $variantId,
                    'warehouse_id' => $warehouseId,
                    'quantity' => $newQuantity
                ]);
            }

            InventoryLog::create([
                'variant_id' => $variantId,
                'warehouse_id' => $warehouseId,
                'change_type' => 'IMPORT',
                'quantity_change' => $qty,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $newQuantity,
                'reference_type' => 'order',
                'reference_id' => $orderId,
                'note' => "Hoàn kho do đơn hàng #{$orderId} bị huỷ",
                'created_by' => auth()->id() ?? 1
            ]);
        });
    }
}
