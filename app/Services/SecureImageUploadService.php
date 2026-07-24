<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class SecureImageUploadService
{
    public const ALLOWED_MIMES = ['jpg', 'jpeg', 'png', 'webp'];

    public function storeWebp(
        UploadedFile $file,
        string $directory,
        int $maxWidth = 1200,
        int $quality = 85
    ): string {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getRealPath());

        if ($image->width() > $maxWidth) {
            $image->scale(width: $maxWidth);
        }

        $path = trim($directory, '/') . '/' . Str::uuid() . '.webp';
        Storage::disk('public')->put($path, $image->toWebp($quality));

        return $path;
    }

    public static function validationRules(bool $required = false, int $maxKilobytes = 2048): array
    {
        return [
            $required ? 'required' : 'nullable',
            'file',
            'image',
            'mimes:' . implode(',', self::ALLOWED_MIMES),
            'max:' . $maxKilobytes,
        ];
    }
}
