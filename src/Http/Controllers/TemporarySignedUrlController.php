<?php

declare(strict_types=1);

namespace CloudMazing\FilamentS3MultipartUpload\Http\Controllers;

use Aws\S3\S3Client;
use Illuminate\Http\Request;

class TemporarySignedUrlController
{
    public function __construct(private S3Client $s3)
    {
    }

    /**
     * Создает временный подписанный URL для загрузки части файла
     *
     * @param Request $request
     * @param string $uploadId
     * @param int $index
     * @return array
     */
    public function show(Request $request, string $uploadId, int $index)
    {
        $diskName = $request->header('X-S3-Disk') ?: config('filament-s3-multipart-upload.default_disk', 's3');
        $bucket = config("filesystems.disks.{$diskName}.bucket");
        
        $command = $this->s3->getCommand('uploadPart', [
            'Bucket' => $bucket,
            'Key' => $request->query('key'),
            'UploadId' => $uploadId,
            'PartNumber' => $index,
            'Body' => '',
        ]);

        $url = (string) $this->s3
            ->createPresignedRequest($command, '+1 hour')
            ->getUri();

        return [
            'url' => $url,
        ];
    }
}
