<?php

declare(strict_types=1);

namespace CloudMazing\FilamentS3MultipartUpload\Http\Controllers;

use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class S3DeleteFileController
{
    /**
     * Конструктор контроллера
     */
    public function __construct(private S3Client $s3)
    {
    }

    /**
     * Удаляет файл из хранилища S3
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteFile(Request $request): JsonResponse
    {
        $key = $request->input('key');
        $diskName = $request->header('X-S3-Disk') ?: config('filament-s3-multipart-upload.default_disk', 's3');
        $bucket = config("filesystems.disks.{$diskName}.bucket");
        
        if (!$key || !$bucket) {
            return response()->json(['error' => 'Отсутствует ключ файла или имя бакета'], 400);
        }
        
        try {
            $this->s3->deleteObject([
                'Bucket' => $bucket,
                'Key' => $key,
            ]);
            
            return response()->json(['success' => true, 'message' => 'Файл успешно удален']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ошибка при удалении файла: ' . $e->getMessage()
            ], 500);
        }
    }
}
