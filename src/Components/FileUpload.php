<?php

namespace Joshembling\ImageOptimizer\Components;

use Closure;
use Filament\Forms\Components\FileUpload as FilamentFileUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Joshembling\ImageOptimizer\Support\ImageProcessor;
use League\Flysystem\UnableToCheckFileExistence;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class FileUpload extends FilamentFileUpload
{
    protected string | Closure | null $optimize = null;

    protected int | Closure | null $resize = null;

    protected int | Closure | null $maxImageWidth = null;

    protected int | Closure | null $maxImageHeight = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getUploadedFileNameForStorageUsing(static function (self $component, TemporaryUploadedFile $file): string {
            return $component->shouldPreserveFilenames()
                ? $file->getClientOriginalName()
                : (Str::ulid() . '.' . $file->getClientOriginalExtension());
        });

        $this->saveUploadedFileUsing(static function (self $component, TemporaryUploadedFile $file): ?string {
            try {
                if (! $file->exists()) {
                    return null;
                }
            } catch (UnableToCheckFileExistence) {
                return null;
            }

            $filename = $component->getUploadedFileNameForStorage($file);

            $optimize = $component->getOptimization();
            $resize = $component->getResize();
            $maxWidth = $component->getMaxImageWidth();
            $maxHeight = $component->getMaxImageHeight();

            if (
                str_contains((string) $file->getMimeType(), 'image') &&
                ($optimize || $resize || $maxWidth || $maxHeight)
            ) {
                $result = ImageProcessor::processTemporaryUpload(
                    file: $file,
                    format: $optimize,
                    resizePercentage: $resize,
                    maxWidth: $maxWidth,
                    maxHeight: $maxHeight,
                );

                if ($result) {
                    $filename = self::formatFileName($filename, $result['format']);

                    $path = trim(($component->getDirectory() ? $component->getDirectory() . '/' : '') . $filename, '/');

                    Storage::disk($component->getDiskName())->put(
                        $path,
                        $result['binary'],
                        ['visibility' => $component->getVisibility()],
                    );

                    return $path;
                }
            }

            $storeMethod = $component->getVisibility() === 'public' ? 'storePubliclyAs' : 'storeAs';

            return $file->{$storeMethod}(
                $component->getDirectory(),
                $filename,
                $component->getDiskName(),
            );
        });
    }

    public function optimize(string | Closure | null $optimize): static
    {
        $this->optimize = $optimize;

        return $this;
    }

    public function resize(int | Closure | null $reductionPercentage): static
    {
        $this->resize = $reductionPercentage;

        return $this;
    }

    public function maxImageWidth(int | Closure | null $width): static
    {
        $this->maxImageWidth = $width;

        return $this;
    }

    public function maxImageHeight(int | Closure | null $height): static
    {
        $this->maxImageHeight = $height;

        return $this;
    }

    public function getOptimization(): ?string
    {
        return $this->evaluate($this->optimize);
    }

    public function getResize(): ?int
    {
        return $this->evaluate($this->resize);
    }

    public function getMaxImageWidth(): ?int
    {
        return $this->evaluate($this->maxImageWidth);
    }

    public function getMaxImageHeight(): ?int
    {
        return $this->evaluate($this->maxImageHeight);
    }

    public static function formatFileName(string $filename, ?string $format): string
    {
        if (! $format) {
            return $filename;
        }

        $format = strtolower($format) === 'jpg' ? 'jpeg' : strtolower($format);

        $extension = strrpos($filename, '.');

        if ($extension !== false) {
            return substr($filename, 0, $extension + 1) . $format;
        }

        return $filename . '.' . $format;
    }
}
