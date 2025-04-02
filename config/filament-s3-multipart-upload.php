<?php

declare(strict_types=1);

return [
    /**
     * Префикс для URL-адресов API компонента
     */
    'prefix' => '_multipart-upload',
    
    /**
     * Диск S3 по умолчанию, который будет использоваться, если не указан другой
     */
    'default_disk' => 's3',
];
