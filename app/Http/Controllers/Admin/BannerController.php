<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Services\HomepageCacheService;
use App\Services\SecureImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('sort_order')->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request, SecureImageUploadService $imageUploadService)
    {
        $request->validate([
            'image' => SecureImageUploadService::validationRules(required: true, maxKilobytes: 3072),
            'type' => 'required|in:MAIN,SECONDARY',
        ]);

        $data = $request->only(['title', 'link_url', 'type', 'sort_order', 'is_active']);
        
        if ($request->hasFile('image')) {
            $path = $imageUploadService->storeWebp($request->file('image'), 'banners', maxWidth: 1920);
            $data['image_url'] = $path;
        }

        Banner::create($data);
        app(HomepageCacheService::class)->flush();

        return redirect()->route('admin.banners.index')->with('success', 'Thêm banner thành công!');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner, SecureImageUploadService $imageUploadService)
    {
        $request->validate([
            'image' => SecureImageUploadService::validationRules(maxKilobytes: 3072),
            'type' => 'required|in:MAIN,SECONDARY',
        ]);

        $data = $request->only(['title', 'link_url', 'type', 'sort_order', 'is_active']);
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            if ($banner->image_url) {
                Storage::disk('public')->delete($banner->image_url);
            }
            $path = $imageUploadService->storeWebp($request->file('image'), 'banners', maxWidth: 1920);
            $data['image_url'] = $path;
        }

        $banner->update($data);
        app(HomepageCacheService::class)->flush();

        return redirect()->route('admin.banners.index')->with('success', 'Cập nhật banner thành công!');
    }

    public function destroy(Banner $banner)
    {
        if ($banner->image_url) {
            Storage::disk('public')->delete($banner->image_url);
        }
        $banner->delete();
        app(HomepageCacheService::class)->flush();

        return redirect()->route('admin.banners.index')->with('success', 'Xóa banner thành công!');
    }
}
