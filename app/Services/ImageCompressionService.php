<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ImageCompressionService
{
    public function store(UploadedFile $file, string $directory, int $maxDimension = 1280, int $quality = 75): string
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('Ekstensi GD PHP wajib aktif untuk memproses foto.');
        }

        $contents = file_get_contents($file->getRealPath());
        $source = $contents === false ? false : imagecreatefromstring($contents);
        if ($source === false) {
            throw new RuntimeException('Foto tidak dapat diproses. Gunakan format JPG, PNG, atau WebP.');
        }

        $source = $this->fixOrientation($source, $file);
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $scale = min(1, $maxDimension / max($sourceWidth, $sourceHeight));
        $targetWidth = max(1, (int) round($sourceWidth * $scale));
        $targetHeight = max(1, (int) round($sourceHeight * $scale));

        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        $white = imagecolorallocate($target, 255, 255, 255);
        imagefill($target, 0, 0, $white);
        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight
        );

        ob_start();
        if (function_exists('imagewebp')) {
            imagewebp($target, null, $quality);
            $extension = 'webp';
        } else {
            imagejpeg($target, null, $quality);
            $extension = 'jpg';
        }
        $compressed = ob_get_clean();

        imagedestroy($source);
        imagedestroy($target);

        if (! is_string($compressed) || $compressed === '') {
            throw new RuntimeException('Foto gagal dikompresi.');
        }

        $path = trim($directory, '/').'/'.Str::uuid().'.'.$extension;
        if (! Storage::disk('public')->put($path, $compressed)) {
            throw new RuntimeException('Foto gagal disimpan ke penyimpanan publik.');
        }

        return $path;
    }

    private function fixOrientation(\GdImage $image, UploadedFile $file): \GdImage
    {
        if (! function_exists('exif_read_data') || ! in_array($file->getMimeType(), ['image/jpeg', 'image/jpg'], true)) {
            return $image;
        }

        $exif = @exif_read_data($file->getRealPath());
        $angle = match ((int) ($exif['Orientation'] ?? 1)) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);
        if ($rotated === false) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }
}
