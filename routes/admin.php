<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\VariantController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\BannerController;

// ===================== ADMIN ROUTES =====================
// File này được load với prefix '/admin' và name 'admin.' từ bootstrap/app.php
// Nên KHÔNG thêm 'admin.' vào tên route ở đây nữa

Route::middleware('admin')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ── Thương hiệu ──
    Route::resource('thuong-hieu', BrandController::class)->names([
        'index'   => 'brands.index',
        'create'  => 'brands.create',
        'store'   => 'brands.store',
        'show'    => 'brands.show',
        'edit'    => 'brands.edit',
        'update'  => 'brands.update',
        'destroy' => 'brands.destroy',
    ]);
    
    // ── Nhà cung cấp ──
    Route::resource('nha-cung-cap', SupplierController::class)->names([
        'index'   => 'suppliers.index',
        'create'  => 'suppliers.create',
        'store'   => 'suppliers.store',
        'edit'    => 'suppliers.edit',
        'update'  => 'suppliers.update',
        'destroy' => 'suppliers.destroy',
    ])->except(['show']);

    // ── Sản phẩm ──
    // Lưu ý: /san-pham/them phải khai báo TRƯỚC /{id} để Laravel không hiểu "them" là {id}
    Route::get('/san-pham',          [ProductController::class, 'index'])   ->name('products.index');
    Route::get('/san-pham/them',     [ProductController::class, 'create'])  ->name('products.create');
    Route::post('/san-pham',         [ProductController::class, 'store'])   ->name('products.store');
    Route::get('/san-pham/{id}/sua', [ProductController::class, 'edit'])    ->name('products.edit');
    Route::put('/san-pham/{id}',     [ProductController::class, 'update'])  ->name('products.update');
    Route::delete('/san-pham/{id}',  [ProductController::class, 'destroy']) ->name('products.destroy');

    // ── Variants ──
    Route::get('/san-pham/{id}/bien-the',  [VariantController::class, 'index']) ->name('products.variants.index');
    Route::post('/san-pham/{id}/bien-the', [VariantController::class, 'store']) ->name('products.variants.store');

    Route::put('/bien-the/{id}',                   [VariantController::class, 'update'])         ->name('variants.update');
    Route::delete('/bien-the/{id}',                [VariantController::class, 'destroy'])         ->name('variants.destroy');
    Route::post('/bien-the/{id}/anh',              [VariantController::class, 'uploadImages'])    ->name('variants.upload-images');
    Route::delete('/bien-the/anh/{imageId}',       [VariantController::class, 'deleteImage'])     ->name('variants.delete-image');
    Route::post('/bien-the/anh/{imageId}/primary', [VariantController::class, 'setPrimaryImage']) ->name('variants.set-primary');

    // ── Tồn kho ──
    Route::get('/ton-kho', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/ton-kho/nhap', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/ton-kho', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/ton-kho/lich-su', [InventoryController::class, 'logs'])->name('inventory.logs');
    
    // ── Coupon ──
    Route::resource('coupons', CouponController::class)->except(['show']);

    // ── Đơn hàng ──
    Route::get('/orders',              [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}',         [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{id}/status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.update_status');
    Route::post('/orders/{id}/cancel', [\App\Http\Controllers\Admin\OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/orders/{id}/invoice', [\App\Http\Controllers\Admin\OrderController::class, 'invoice'])->name('orders.invoice');

    // ── Đánh giá ──
    Route::get('/reviews',              [\App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/{id}/approve', [\App\Http\Controllers\Admin\ReviewController::class, 'approve'])->name('reviews.approve');
    Route::delete('/reviews/{id}',       [\App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');

    // ── Người dùng ──
    Route::get('/users',               [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::post('/users/{id}/toggle',  [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle_status');

    // ── Banners ──
    Route::resource('banners', BannerController::class)->names([
        'index'   => 'banners.index',
        'create'  => 'banners.create',
        'store'   => 'banners.store',
        'edit'    => 'banners.edit',
        'update'  => 'banners.update',
        'destroy' => 'banners.destroy',
    ])->except(['show']);

    // ── Cấu hình ──
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

    // ── Báo cáo & Xuất dữ liệu ──
    Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/orders/export', [\App\Http\Controllers\Admin\ReportController::class, 'exportOrders'])->name('reports.orders.export');
    Route::get('/reports/inventory/export', [\App\Http\Controllers\Admin\ReportController::class, 'exportInventory'])->name('reports.inventory.export');

});