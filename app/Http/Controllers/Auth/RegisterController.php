<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Hiển thị form đăng ký
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Xử lý đăng ký tài khoản
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'              => ['required', 'confirmed', Password::defaults()],
            'phone'                 => ['nullable', 'string', 'max:20'],
        ]);

        try {
            $user = User::create([
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'password'  => Hash::make($validated['password']),
                'phone'     => $validated['phone'] ?? null,
                'role'      => 'customer',
            ]);

            Auth::login($user);

            return redirect()->route('home')
                            ->with('success', 'Đăng ký tài khoản thành công!');

        } catch (\Exception $e) {
            return back()
                    ->withInput()
                    ->with('error', 'Đăng ký thất bại: ' . $e->getMessage());
        }
    }
}