<?php

declare(strict_types=1);

namespace CloudMazing\FilamentS3MultipartUpload\Http\Controllers;

use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class S3SignedUrlController
{
    /**
     * Конструктор контроллера
     */
    public function __construct(private S3Client $s3)
    {
    }

    /**
     * Создает подписанный URL для просмотра файла из S3
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSignedUrl(Request $request): JsonResponse
    {
        $key = $request->query('key');
        $diskName = $request->header('X-S3-Disk') ?: config('filament-s3-multipart-upload.default_disk', 's3');
        $bucket = config("filesystems.disks.{$diskName}.bucket");
        
        if (!$key || !$bucket) {
            return response()->json(['error' => 'Отсутствуют необходимые параметры'], 400);
        }
        
        try {
            $command = $this->s3->getCommand('GetObject', [
                'Bucket' => $bucket,
                'Key' => $key,
            ]);
            
            $url = (string) $this->s3
                ->createPresignedRequest($command, '+1 hour')
                ->getUri();
            
            return response()->json(['url' => $url]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка при создании подписанного URL: ' . $e->getMessage()], 500);
        }
    }
}
