<?php

declare(strict_types=1);

use CloudMazing\FilamentS3MultipartUpload\Http\Controllers\MultipartUploadCompletionController;
use CloudMazing\FilamentS3MultipartUpload\Http\Controllers\MultipartUploadController;
use CloudMazing\FilamentS3MultipartUpload\Http\Controllers\S3DeleteFileController;
use CloudMazing\FilamentS3MultipartUpload\Http\Controllers\S3SignedUrlController;
use CloudMazing\FilamentS3MultipartUpload\Http\Controllers\TemporarySignedUrlController;
use Illuminate\Support\Facades\Route;

// Маршруты для мультичастной загрузки S3
Route::prefix(config('filament-s3-multipart-upload.prefix').'/s3')->name('filament.')->group(function () {
    Route::post('multipart', [MultipartUploadController::class, 'store'])->name('multipart-upload.store');

    Route::get('multipart/{uploadId}/{id}', [TemporarySignedUrlController::class, 'show'])->name('multipart-upload.temporary-signed-url.store');

    Route::post('multipart/{uploadId}/complete', [MultipartUploadCompletionController::class, 'store'])->name('multipart-upload.completion.store');
});

// API маршруты для работы с файлами в S3
Route::prefix('api')->group(function () {
    // Маршрут для получения подписанного URL для просмотра файла
    Route::get('s3-signed-url', [S3SignedUrlController::class, 'getSignedUrl'])->name('s3.signed-url');
    
    // Маршрут для удаления файла из S3
    Route::delete('s3-delete-file', [S3DeleteFileController::class, 'deleteFile'])->name('s3.delete-file');
});
