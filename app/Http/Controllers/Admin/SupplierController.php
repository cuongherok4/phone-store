<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
        }

        $suppliers = $query->latest()->paginate(15);
        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('admin.suppliers.form', ['supplier' => new Supplier(['is_active' => true])]);
    }

    public function store(SupplierRequest $request)
    {
        Supplier::create($request->validated());
        return redirect()->route('admin.suppliers.index')->with('success', 'Đã thêm nhà cung cấp thành công.');
    }

    public function edit(Supplier $nha_cung_cap)
    {
        return view('admin.suppliers.form', ['supplier' => $nha_cung_cap]);
    }

    public function update(SupplierRequest $request, Supplier $nha_cung_cap)
    {
        $nha_cung_cap->update($request->validated());
        return redirect()->route('admin.suppliers.index')->with('success', 'Đã cập nhật nhà cung cấp thành công.');
    }

    public function destroy(Supplier $nha_cung_cap)
    {
        // Kiểm tra xem nhà cung cấp có đang liên kết với lô hàng nào không
        // if ($nha_cung_cap->inventoryLogs()->exists()) {
        //     return back()->with('error', 'Không thể xóa nhà cung cấp đã có lịch sử nhập hàng.');
        // }

        $nha_cung_cap->delete();
        return redirect()->route('admin.suppliers.index')->with('success', 'Đã xóa nhà cung cấp thành công.');
    }
}