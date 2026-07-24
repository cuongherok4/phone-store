<?php

namespace Tests\Unit\Services;

use App\Services\SecureImageUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Tests\TestCase;

class SecureImageUploadServiceTest extends TestCase
{
    public function test_it_reencodes_uploaded_images_as_webp_with_safe_name(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('logo.png', 900, 600);
        $path = app(SecureImageUploadService::class)->storeWebp($file, 'brands', maxWidth: 400);

        Storage::disk('public')->assertExists($path);
        $this->assertStringStartsWith('brands/', $path);
        $this->assertStringEndsWith('.webp', $path);
        $this->assertDoesNotMatchRegularExpression('/logo\\.png$/', $path);

        $image = (new ImageManager(new Driver()))->read(Storage::disk('public')->path($path));
        $this->assertSame(400, $image->width());
    }

    public function test_validation_rules_reject_unsupported_image_formats(): void
    {
        $validator = Validator::make([
            'image' => UploadedFile::fake()->image('banner.gif'),
        ], [
            'image' => SecureImageUploadService::validationRules(required: true),
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('image', $validator->errors()->toArray());
    }
}
