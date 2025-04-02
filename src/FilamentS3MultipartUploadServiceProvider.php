<?php

declare(strict_types=1);

namespace CloudMazing\FilamentS3MultipartUpload;

use Aws\S3\S3Client;
use CloudMazing\FilamentS3MultipartUpload\Http\Controllers\MultipartUploadCompletionController;
use CloudMazing\FilamentS3MultipartUpload\Http\Controllers\MultipartUploadController;
use CloudMazing\FilamentS3MultipartUpload\Http\Controllers\TemporarySignedUrlController;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Filesystem\FilesystemManager;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Filament\Support\Assets\Css;

class FilamentS3MultipartUploadServiceProvider extends PackageServiceProvider
{
    public static string $name = 's3-multipart-upload';

    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-s3-multipart-upload')
            ->hasConfigFile()
            ->hasViews()
            ->hasAssets()
            ->hasRoutes('web');
    }

    public function boot(): void
    {
        parent::boot();

        // Регистрируем скрипт для компонента
        FilamentAsset::register([
            AlpineComponent::make('uppy', __DIR__ . '/../resources/js/dist/components/uppy.js')
                ->loadedOnRequest(),
        ], 'cloudmazing/filament-s3-multipart-upload');

        // Динамически задаем S3 клиент на основе диска из запроса или конфигурации
        $this->app->bind(S3Client::class, function ($app) {
            $request = $app->make('request');
            $disk = $request->header('X-S3-Disk') ?: config('filament-s3-multipart-upload.default_disk', 's3');
            
            return $app->make(FilesystemManager::class)->disk($disk)->getClient();
        });
    }
}
