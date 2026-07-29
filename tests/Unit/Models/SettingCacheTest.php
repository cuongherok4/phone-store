<?php

namespace Tests\Unit\Models;

use App\Models\Setting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SettingCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('settings');
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Cache::flush();
    }

    public function test_it_reads_settings_from_cache_after_first_lookup(): void
    {
        Setting::create(['key' => 'store_phone', 'value' => '0900000000']);

        $this->assertSame('0900000000', Setting::get('store_phone'));

        DB::table('settings')
            ->where('key', 'store_phone')
            ->update(['value' => '0911111111']);

        $this->assertSame('0900000000', Setting::get('store_phone'));
    }

    public function test_it_flushes_cache_when_setting_is_updated(): void
    {
        $setting = Setting::create(['key' => 'store_phone', 'value' => '0900000000']);

        $this->assertSame('0900000000', Setting::get('store_phone'));

        $setting->update(['value' => '0911111111']);

        $this->assertSame('0911111111', Setting::get('store_phone'));
    }
}
