<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Customer\ProductController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\AIController;
use App\Http\Controllers\Auth\SocialController;

// ===================== SOCIAL LOGIN =====================
Route::get('/auth/{provider}/redirect', [SocialController::class, 'redirectToProvider'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialController::class, 'handleProviderCallback'])->name('social.callback');

// ===================== TRANG CHỦ =====================
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::redirect('/home', '/');

// ===================== SẢN PHẨM =====================
Route::get('/san-pham',           [ProductController::class, 'index'])->name('customer.products.index');
Route::get('/san-pham/{slug}',    [ProductController::class, 'show'])->name('customer.products.show');
Route::get('/thuong-hieu/{slug}', [ProductController::class, 'byBrand'])->name('customer.products.byBrand');
Route::get('/danh-muc/{slug}',    [ProductController::class, 'byCategory'])->name('customer.products.byCategory');
Route::post('/ai/consult',        [AIController::class, 'consult'])->name('customer.ai.consult');

// ===================== AUTH ROUTES =====================
Route::middleware(['web', 'auth'])->group(function () {
    
    // GIỎ HÀNG
    Route::get('/gio-hang',               [CartController::class, 'index'])->name('cart.index');
    Route::post('/gio-hang/them',         [CartController::class, 'add'])->name('cart.add');
    Route::put('/gio-hang/cap-nhat/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/gio-hang/xoa/{id}',   [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/gio-hang/count',         [CartController::class, 'count'])->name('cart.count');
    Route::post('/gio-hang/toggle/{id}',  [CartController::class, 'toggleSelection'])->name('cart.toggle');
    Route::post('/gio-hang/toggle-all',   [CartController::class, 'toggleAll'])->name('cart.toggle_all');

    // THANH TOÁN
    Route::get('/thanh-toan',             [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/thanh-toan/dat-hang',    [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/thanh-toan/thanh-cong/{id}', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::post('/thanh-toan/check-coupon', [CheckoutController::class, 'checkCoupon'])->name('checkout.check_coupon');
    Route::get('/thanh-toan/demo-gateway', [CheckoutController::class, 'demoGateway'])->name('checkout.demo_gateway');
    Route::get('/thanh-toan/demo-callback', [CheckoutController::class, 'demoCallback'])->name('checkout.demo_callback');

    // Đơn hàng của tôi
    Route::get('/don-hang',              [\App\Http\Controllers\Customer\OrderController::class, 'index'])->name('orders.index');
    Route::get('/don-hang/{id}',         [\App\Http\Controllers\Customer\OrderController::class, 'show'])->name('orders.show');
    Route::post('/don-hang/{id}/huy',    [\App\Http\Controllers\Customer\OrderController::class, 'cancel'])->name('orders.cancel');

    // Yêu thích (Wishlist)
    Route::get('/yeu-thich',              [\App\Http\Controllers\Customer\WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/yeu-thich/toggle',     [\App\Http\Controllers\Customer\WishlistController::class, 'toggle'])->name('wishlist.toggle');

    // Đánh giá (Reviews)
    Route::get('/danh-gia',               [\App\Http\Controllers\Customer\ReviewController::class, 'index'])->name('reviews.index');
    Route::post('/danh-gia/gui',          [\App\Http\Controllers\Customer\ReviewController::class, 'store'])->name('reviews.store');

    // Thông báo (Notifications)
    Route::get('/thong-bao',              [\App\Http\Controllers\Customer\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/thong-bao/{id}/read',   [\App\Http\Controllers\Customer\NotificationController::class, 'markAsRead'])->name('notifications.mark_read');
    Route::post('/thong-bao/read-all',    [\App\Http\Controllers\Customer\NotificationController::class, 'markAllAsRead'])->name('notifications.read_all');

    // Hồ sơ cá nhân (Profile)
    Route::get('/ho-so',                  [\App\Http\Controllers\Customer\ProfileController::class, 'index'])->name('profile.index');
    Route::post('/ho-so/cap-nhat',        [\App\Http\Controllers\Customer\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/ho-so/avatar',          [\App\Http\Controllers\Customer\ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::post('/ho-so/mat-khau',        [\App\Http\Controllers\Customer\ProfileController::class, 'updatePassword'])->name('profile.password');
    
    // Quản lý địa chỉ
    Route::post('/ho-so/dia-chi',               [\App\Http\Controllers\Customer\ProfileController::class, 'storeAddress'])->name('profile.address.store');
    Route::delete('/ho-so/dia-chi/{id}',         [\App\Http\Controllers\Customer\ProfileController::class, 'destroyAddress'])->name('profile.address.destroy');
    Route::post('/ho-so/dia-chi/{id}/default',  [\App\Http\Controllers\Customer\ProfileController::class, 'setDefaultAddress'])->name('profile.address.default');

});

// ===================== GUEST ONLY ROUTES =====================
Route::middleware(['web', 'guest'])->group(function () {
    Route::get('/login',    [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login',   [LoginController::class, 'login']);

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// ===================== PAYMENT CALLBACKS (Public) =====================
// Các route này nhận kết quả từ Gateway (MoMo, VNPAY)
// Cần để ngoài auth middleware và tắt CSRF
Route::match(['get', 'post'], '/thanh-toan/vnpay-callback', [CheckoutController::class, 'vnpayCallback'])->name('checkout.vnpay_callback');
