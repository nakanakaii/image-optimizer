<?php

namespace Joshembling\ImageOptimizer\Support;

use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class ImageProcessor
{
    /**
     * @return array{binary: string, format: string}|null
     */
    public static function processTemporaryUpload(
        TemporaryUploadedFile $file,
        ?string $format = null,
        ?int $resizePercentage = null,
        ?int $maxWidth = null,
        ?int $maxHeight = null,
    ): ?array {
        $mime = (string) ($file->getMimeType() ?? '');

        if (! str_contains($mime, 'image')) {
            return null;
        }

        $path = $file->getRealPath();

        if (! $path) {
            return null;
        }

        $manager = self::manager();
        $image = $manager->read($path);

        // Percent-based reduction (keeps aspect ratio).
        if (filled($resizePercentage)) {
            $scale = max(0, min(100, $resizePercentage)) / 100;
            $targetFactor = 1 - $scale;

            $width = $image->width();
            $height = $image->height();

            if ($height > $width) {
                $targetHeight = max(1, (int) round($height * $targetFactor));
                $image = $image->scale(height: $targetHeight);
            } else {
                $targetWidth = max(1, (int) round($width * $targetFactor));
                $image = $image->scale(width: $targetWidth);
            }
        }

        // Max bounds (keeps aspect ratio, won't upscale).
        if (filled($maxWidth) || filled($maxHeight)) {
            $image = $image->scaleDown(
                width: filled($maxWidth) ? $maxWidth : null,
                height: filled($maxHeight) ? $maxHeight : null,
            );
        }

        $targetFormat = self::normalizeFormat($format ?? self::formatFromMime($mime) ?? 'jpeg');
        $quality = self::qualityFor($targetFormat);

        $encoded = match ($targetFormat) {
            'webp' => $image->toWebp(quality: $quality),
            'avif' => $image->toAvif(quality: $quality),
            'png' => $image->toPng(), // lossless (quality not applicable)
            'gif' => $image->toGif(),
            'jpeg' => $image->toJpeg(quality: $quality),
            default => $image->toJpeg(quality: $quality),
        };

        return [
            'binary' => (string) $encoded,
            'format' => $targetFormat,
        ];
    }

    private static function manager(): ImageManager
    {
        $driver = strtolower((string) config('image-optimizer.driver', 'gd'));

        return new ImageManager(
            match ($driver) {
                'imagick' => new ImagickDriver,
                default => new GdDriver,
            },
        );
    }

    private static function qualityFor(string $format): int
    {
        $format = self::normalizeFormat($format);

        return (int) match ($format) {
            'jpeg' => config('image-optimizer.quality.jpeg', 70),
            'webp' => config('image-optimizer.quality.webp', 75),
            'avif' => config('image-optimizer.quality.avif', 50),
            default => 75,
        };
    }

    private static function formatFromMime(string $mime): ?string
    {
        return match (strtolower($mime)) {
            'image/jpg', 'image/jpeg' => 'jpeg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/avif' => 'avif',
            'image/gif' => 'gif',
            default => null,
        };
    }

    private static function normalizeFormat(string $format): string
    {
        $format = strtolower(trim($format));

        return match ($format) {
            'jpg' => 'jpeg',
            default => $format,
        };
    }
}
