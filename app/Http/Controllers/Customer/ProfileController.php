<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Trang hồ sơ cá nhân.
     */
    public function index()
    {
        $user = Auth::user();
        $addresses = $user->addresses()->orderBy('is_default', 'desc')->get();
        return view('customer.profile.index', compact('user', 'addresses'));
    }

    /**
     * Cập nhật thông tin cá nhân.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($request->only('name', 'phone'));

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }

    /**
     * Cập nhật avatar.
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|max:1024',
        ]);

        $user = Auth::user();
        
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return back()->with('success', 'Cập nhật ảnh đại diện thành công!');
    }

    /**
     * Đổi mật khẩu.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không chính xác.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }

    /**
     * Thêm địa chỉ mới.
     */
    public function storeAddress(Request $request)
    {
        $request->validate([
            'receiver_name'  => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'province'       => 'required|string|max:100',
            'district'       => 'required|string|max:100',
            'ward'           => 'required|string|max:100',
            'address_detail' => 'required|string|max:500',
        ]);

        $user = Auth::user();
        
        $isDefault = $user->addresses()->count() === 0 ? true : (bool)$request->is_default;

        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create(array_merge($request->all(), ['is_default' => $isDefault]));

        return back()->with('success', 'Đã thêm địa chỉ mới.');
    }

    /**
     * Xoá địa chỉ.
     */
    public function destroyAddress($id)
    {
        $address = Auth::user()->addresses()->findOrFail($id);
        
        if ($address->is_default) {
            return back()->with('error', 'Không thể xoá địa chỉ mặc định.');
        }

        $address->delete();
        return back()->with('success', 'Đã xoá địa chỉ.');
    }

    /**
     * Đặt làm địa chỉ mặc định.
     */
    public function setDefaultAddress($id)
    {
        $user = Auth::user();
        $user->addresses()->update(['is_default' => false]);
        $user->addresses()->findOrFail($id)->update(['is_default' => true]);

        return back()->with('success', 'Đã thiết lập địa chỉ mặc định.');
    }
}
