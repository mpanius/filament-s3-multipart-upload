<?php

declare(strict_types=1);

namespace CloudMazing\FilamentS3MultipartUpload\Http\Controllers;

use Aws\S3\S3Client;
use Illuminate\Http\Request;

class MultipartUploadCompletionController
{
    public function __construct(private S3Client $s3)
    {
    }

    /**
     * Завершает мультичастную загрузку в S3
     *
     * @param Request $request
     * @param string $uploadId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, string $uploadId)
    {
        $diskName = $request->header('X-S3-Disk') ?: config('filament-s3-multipart-upload.default_disk', 's3');
        $bucket = config("filesystems.disks.{$diskName}.bucket");
        
        $result = $this->s3->completeMultipartUpload([
            'Bucket' => $bucket,
            'Key' => $request->query('key'),
            'UploadId' => $uploadId,
            'MultipartUpload' => ['Parts' => $request->input('parts')],
        ]);

        return response()->json([
            'path' => $result->get('Key'),
            'url' => $result->get('Location'),
            'disk' => $diskName,
        ]);
    }
}
