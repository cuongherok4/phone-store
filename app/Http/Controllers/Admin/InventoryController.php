<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Danh sách tồn kho
     */
    public function index(Request $request)
    {
        $query = Inventory::with(['variant.product', 'variant.images', 'warehouse']);

        // Tìm kiếm theo tên sản phẩm hoặc SKU
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('variant', function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $inventories = $query->paginate(15);

        return view('admin.inventory.index', compact('inventories'));
    }

    /**
     * Form nhập hàng
     */
    public function create()
    {
        // Eager load product để lấy tên sản phẩm + thuộc tính (nếu có) hiển thị trên select
        $variants = ProductVariant::with(['product', 'images', 'variantAttributes.attributeValue'])->get();
        $suppliers = Supplier::where('is_active', true)->get();
        return view('admin.inventory.create', compact('variants', 'suppliers'));
    }

    /**
     * Xử lý nhập hàng
     */
    public function store(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'quantity' => 'required|integer|min:1',
            'import_price' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:255',
        ], [
            'variant_id.required' => 'Vui lòng chọn sản phẩm (phiên bản)',
            'supplier_id.required' => 'Vui lòng chọn nhà cung cấp',
            'quantity.required' => 'Vui lòng nhập số lượng',
            'quantity.min' => 'Số lượng phải lớn hơn 0',
        ]);

        try {
            $defaultWarehouseId = 1; // Mặc định sử dụng kho chính
            $this->inventoryService->import(
                $request->variant_id,
                $defaultWarehouseId,
                $request->quantity,
                $request->note ?? "Nhập hàng",
                auth()->id() ?? 1, // fallback nếu lỗi session
                $request->supplier_id,
                $request->import_price
            );

            return redirect()->route('admin.inventory.index')->with('success', 'Nhập hàng thành công.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Xem lịch sử tồn kho (logs)
     */
    public function logs(Request $request)
    {
        $query = InventoryLog::with(['variant.product', 'variant.images', 'warehouse', 'createdBy', 'supplier']);

        // Lọc theo loại thay đổi
        if ($request->filled('change_type')) {
            $query->where('change_type', $request->change_type);
        }

        // Tìm kiếm theo tên sản phẩm hoặc SKU
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('variant', function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.inventory.logs', compact('logs'));
    }
}
