<?php
// app/Http/Controllers/Admin/ProductController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['brand'])
            ->withCount('variants')
            ->withTrashed(); // hiện cả soft-deleted để admin thấy

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }
        if ($request->filled('status')) {
            if ($request->status === 'trashed') {
                $query->onlyTrashed();
            } else {
                $query->whereNull('deleted_at')->where('status', $request->status);
            }
        } else {
            $query->whereNull('deleted_at');
        }

        $products   = $query->latest()->paginate(15)->withQueryString();
        $brands     = Brand::where('is_active', true)->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'brands'));
    }

    public function create()
    {
        $brands     = Brand::where('is_active', true)->orderBy('name')->get();

        return view('admin.products.create', compact('brands'));
    }

    public function store(StoreProductRequest $request)
    {
        Product::create($request->validated());

        return redirect()->route('admin.products.index')
            ->with('success', 'Thêm sản phẩm thành công!');
    }

    public function edit(int $id)
    {
        $product    = Product::withTrashed()->findOrFail($id);
        $brands     = Brand::where('is_active', true)->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'brands'));
    }

    public function update(StoreProductRequest $request, int $id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->update($request->validated());

        return redirect()->route('admin.products.index')
            ->with('success', 'Cập nhật sản phẩm thành công!');
    }

    public function destroy(int $id)
    {
        $product = Product::findOrFail($id);
        $product->delete(); // soft delete, variants cascade

        return redirect()->route('admin.products.index')
            ->with('success', 'Đã xoá sản phẩm.');
    }
}