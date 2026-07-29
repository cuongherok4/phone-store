<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandRequest;
use App\Models\Brand;
use App\Services\HomepageCacheService;
use App\Services\SecureImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        $brands = Brand::orderBy('name')->paginate(15);
        return view('admin.brands.index', compact('brands'));
    }

    public function create(): View
    {
        return view('admin.brands.form');
    }

    public function store(BrandRequest $request, SecureImageUploadService $imageUploadService): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo'] = $imageUploadService->storeWebp($request->file('logo'), 'brands', maxWidth: 400);
        }

        Brand::create($data);
        app(HomepageCacheService::class)->flush();

        return redirect()->route('admin.brands.index')
            ->with('success', 'Thương hiệu đã được tạo thành công.');
    }

    public function edit(Brand $thuong_hieu): View
    {
        return view('admin.brands.form', ['brand' => $thuong_hieu]);
    }

    public function update(BrandRequest $request, Brand $thuong_hieu, SecureImageUploadService $imageUploadService): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            if ($thuong_hieu->logo) {
                Storage::disk('public')->delete($thuong_hieu->logo);
            }
            $data['logo'] = $imageUploadService->storeWebp($request->file('logo'), 'brands', maxWidth: 400);
        } elseif ($request->boolean('delete_logo')) {
            if ($thuong_hieu->logo) {
                Storage::disk('public')->delete($thuong_hieu->logo);
            }
            $data['logo'] = null;
        }

        $thuong_hieu->update($data);
        app(HomepageCacheService::class)->flush();

        return redirect()->route('admin.brands.index')
            ->with('success', 'Thương hiệu đã được cập nhật thành công.');
    }

    public function destroy(Brand $thuong_hieu): RedirectResponse
    {
        if ($thuong_hieu->products()->count() > 0) {
            return redirect()->route('admin.brands.index')
                ->with('error', 'Không thể xóa thương hiệu đang có sản phẩm.');
        }

        if ($thuong_hieu->logo) {
            Storage::disk('public')->delete($thuong_hieu->logo);
        }

        $thuong_hieu->delete();
        app(HomepageCacheService::class)->flush();

        return redirect()->route('admin.brands.index')
            ->with('success', 'Thương hiệu đã được xóa thành công.');
    }

}
