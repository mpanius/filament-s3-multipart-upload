<?php

declare(strict_types=1);

namespace CloudMazing\FilamentS3MultipartUpload\Http\Controllers;

use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class MultipartUploadController
{
    public function __construct(private S3Client $s3)
    {
    }

    /**
     * Создает мультичастную загрузку в S3
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $diskName = $request->header('X-S3-Disk') ?: config('filament-s3-multipart-upload.default_disk', 's3');
        $bucket = config("filesystems.disks.{$diskName}.bucket");
        
        $response = $this->s3->createMultipartUpload([
            'Bucket' => $bucket,
            'Key' => $request->input('filename'),
            'ContentType' => $request->input('metadata.type'),
            'ContentDisposition' => 'inline',
        ]);

        return response()->json([
            'uploadId' => $response->get('UploadId'),
            'key' => $response->get('Key'),
        ]);
    }
}
