<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\ProductVariant;
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
     * Kiểm tra tồn kho cho nhiều variant trong cùng một transaction.
     * Khi $lockForUpdate = true, các dòng inventory liên quan sẽ bị khóa
     * để tránh hai checkout đồng thời cùng nhìn thấy một lượng tồn kho cũ.
     */
    public function assertManyAvailable(array $items, bool $lockForUpdate = false): void
    {
        $requiredItems = collect($items)
            ->map(fn ($item) => [
                'variant_id' => (int) $item['variant_id'],
                'quantity'   => (int) $item['quantity'],
                'name'       => $item['name'] ?? 'Sản phẩm',
            ])
            ->filter(fn ($item) => $item['variant_id'] > 0 && $item['quantity'] > 0)
            ->groupBy('variant_id')
            ->map(fn ($group) => [
                'variant_id' => $group->first()['variant_id'],
                'quantity'   => $group->sum('quantity'),
                'name'       => $group->first()['name'],
            ])
            ->values();

        if ($requiredItems->isEmpty()) {
            throw new Exception('Không có sản phẩm hợp lệ để kiểm tra tồn kho.');
        }

        $variantIds = $requiredItems->pluck('variant_id')->all();
        $query = Inventory::whereIn('variant_id', $variantIds)
            ->orderBy('variant_id')
            ->orderBy('warehouse_id');

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $availableByVariant = $query->get()
            ->groupBy('variant_id')
            ->map(fn ($rows) => (int) $rows->sum('quantity'));

        foreach ($requiredItems as $item) {
            $available = $availableByVariant->get($item['variant_id'], 0);

            if ($available < $item['quantity']) {
                throw new Exception("Sản phẩm \"{$item['name']}\" hiện chỉ còn {$available} sản phẩm.");
            }
        }
    }

    public function assertAvailable(int $variantId, int $qty, string $name = 'Sản phẩm', bool $lockForUpdate = false): void
    {
        $this->assertManyAvailable([[
            'variant_id' => $variantId,
            'quantity'   => $qty,
            'name'       => $name,
        ]], $lockForUpdate);
    }

    /**
     * Trừ tồn kho khi có đơn hàng (ưu tiên kho có nhiều hàng nhất)
     */
    public function deduct(int $variantId, int $qty, int $orderId): void
    {
        if ($qty <= 0) return;

        DB::transaction(function () use ($variantId, $qty, $orderId) {
            // Khóa các dòng kho trước khi tính tổng để tránh oversell khi nhiều checkout đồng thời.
            $inventories = Inventory::where('variant_id', $variantId)
                ->where('quantity', '>', 0)
                ->orderBy('quantity', 'desc')
                ->lockForUpdate()
                ->get();

            $totalStock = $inventories->sum('quantity');
            if ($totalStock < $qty) {
                throw new Exception("Không đủ số lượng tồn kho để trừ.");
            }

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
                    'quantity_after' => $newQuantity,
                    'reference_type' => 'order',
                    'reference_id' => $orderId,
                    'note' => "Xuất kho cho đơn hàng #{$orderId}",
                    'created_by' => auth()->id() ?? 1 // Fallback cho hệ thống nếu không auth
                ]);

                $remainingQtyToDeduct -= $qtyToDeductFromThisWarehouse;
            }

            if ($remainingQtyToDeduct > 0) {
                throw new Exception("Không đủ số lượng tồn kho để trừ.");
            }

            // Sync lại cột total_stock ở ProductVariant
            ProductVariant::find($variantId)?->syncStock();
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

            // Sync lại cột total_stock ở ProductVariant
            ProductVariant::find($variantId)?->syncStock();
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

            // Sync lại cột total_stock ở ProductVariant
            ProductVariant::find($variantId)?->syncStock();
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
                'change_type' => 'RETURN',
                'quantity_change' => $qty,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $newQuantity,
                'reference_type' => 'order',
                'reference_id' => $orderId,
                'note' => "Hoàn kho do đơn hàng #{$orderId} bị huỷ",
                'created_by' => auth()->id() ?? 1
            ]);

            // Sync lại cột total_stock ở ProductVariant
            ProductVariant::find($variantId)?->syncStock();
        });
    }
}
