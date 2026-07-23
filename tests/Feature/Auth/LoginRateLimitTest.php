<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LoginRateLimitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('customer@example.com|127.0.0.1');
        Cache::flush();

        Schema::dropIfExists('carts');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('role')->default('customer');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->nullable();
            $table->string('key')->unique();
            $table->text('value')->nullable();
        });
    }

    public function test_login_is_rate_limited_after_too_many_failed_attempts(): void
    {
        User::create([
            'name' => 'Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('correct-password'),
            'role' => 'customer',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->from(route('login'))->post('/login', [
                'email' => 'customer@example.com',
                'password' => 'wrong-password',
            ])->assertRedirect(route('login'));
        }

        $this->from(route('login'))->post('/login', [
            'email' => 'customer@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');
    }

    public function test_successful_login_clears_rate_limit(): void
    {
        User::create([
            'name' => 'Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('correct-password'),
            'role' => 'customer',
        ]);

        $this->from(route('login'))->post('/login', [
            'email' => 'customer@example.com',
            'password' => 'wrong-password',
        ])->assertRedirect(route('login'));

        $this->post('/login', [
            'email' => 'customer@example.com',
            'password' => 'correct-password',
        ])->assertRedirect(route('home'));

        $this->assertSame(0, RateLimiter::attempts('customer@example.com|127.0.0.1'));
    }
}
