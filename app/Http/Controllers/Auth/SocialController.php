<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialController extends Controller
{
    /**
     * Redirect to provider
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle provider callback
     */
    public function handleProviderCallback($provider, CartService $cartService)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Đăng nhập thất bại, vui lòng thử lại.');
        }

        // Tìm user theo provider_id
        $user = User::where('provider', $provider)
                    ->where('provider_id', $socialUser->getId())
                    ->first();

        if (!$user) {
            // Nếu không tìm thấy theo provider_id, tìm theo email
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // Nếu tìm thấy email, cập nhật provider_id
                $user->update([
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'provider_token' => $socialUser->token,
                    'provider_refresh_token' => $socialUser->refreshToken,
                ]);
            } else {
                // Nếu chưa có user nào, tạo mới
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'avatar' => $socialUser->getAvatar(),
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'provider_token' => $socialUser->token,
                    'provider_refresh_token' => $socialUser->refreshToken,
                    'password' => null, // Không có password cho social login ban đầu
                    'role' => 'customer',
                ]);
            }
        } else {
            // Cập nhật token mới
            $user->update([
                'provider_token' => $socialUser->token,
                'provider_refresh_token' => $socialUser->refreshToken,
            ]);
        }

        $guestSessionId = session()->getId();
        Auth::login($user);
        session()->regenerate();

        // Merge giỏ hàng
        $cartService->mergeCart($user->id, $guestSessionId);

        return redirect()->intended($user->getRedirectRoute());
    }
}
