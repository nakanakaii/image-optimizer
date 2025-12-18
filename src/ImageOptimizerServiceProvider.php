<?php

namespace Joshembling\ImageOptimizer;

use Illuminate\Filesystem\Filesystem;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ImageOptimizerServiceProvider extends PackageServiceProvider
{
    public static string $name = 'image-optimizer';

    // NOTE: Filament v4: do not alias/override Filament core classes.
    // Users should import Joshembling\ImageOptimizer\Components\FileUpload (and/or SpatieMediaLibraryFileUpload) explicitly.

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('joshembling/image-optimizer');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }
    }

    public function packageBooted(): void
    {
        // Handle Stubs
        if (app()->runningInConsole()) {
            $stubsPath = __DIR__ . '/../stubs/';

            if (is_dir($stubsPath)) {
                foreach (app(Filesystem::class)->files($stubsPath) as $file) {
                    $this->publishes([
                        $file->getRealPath() => base_path("stubs/image-optimizer/{$file->getFilename()}"),
                    ], 'image-optimizer-stubs');
                }
            }
        }
    }
}
