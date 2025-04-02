# Filament S3 Multipart Upload

Филл-компонент для Filament 3+, использующий [Uppy](https://uppy.io) для мультичастной загрузки файлов в Amazon S3 или совместимые хранилища (MinIO, DigitalOcean Spaces, и т.д.). Позволяет загружать файлы большого размера с поддержкой возобновления прерванных загрузок.  

## Основные возможности

- Мультичастная загрузка больших файлов через API Amazon S3
- Поддержка динамического выбора диска хранения
- Возможность задания пути загрузки через callback-функцию
- Предпросмотр загруженных файлов с возможностью просмотра и удаления
- Соответствие стандартам и рекомендациям Filament 3+

## Установка

```sh
composer require cloudmazing/filament-s3-multipart-upload
```

## Основное использование

```php
use CloudMazing\FilamentS3MultipartUpload\Components\FileUpload;

FileUpload::make('column_name')
    ->maxFileSize(10 * 1024 * 1024 * 1024) // 10GB
    ->multiple()
    ->maxNumberOfFiles(5)
    ->directory('uploads/files');
```

## Использование новых возможностей

### Динамический выбор диска

Компонент позволяет выбрать диск, который будет использоваться для загрузки файлов. По умолчанию используется диск `s3`, указанный в конфигурации.

```php
// Явное указание диска
FileUpload::make('column_name')
    ->disk('minio');

// Динамическое указание диска через callback-функцию
FileUpload::make('column_name')
    ->disk(function () {
        return Auth::user()->team->disk_name;
    });
```

### Динамическое указание пути загрузки

Можно динамически задавать путь для загрузки файлов через callback-функцию:

```php
// Простой путь
FileUpload::make('column_name')
    ->directory('uploads/documents');
    
// Динамический путь на основе данных
FileUpload::make('column_name')
    ->directory(function (Get $get) {
        $projectId = $get('project_id');
        return "projects/{$projectId}/documents";
    });
    
// Использование модели формы
FileUpload::make('background_image')
    ->directory(function ($record) {
        return $record ? "products/{$record->id}/images" : 'products/temp';
    });
```

### Конфигурация диска по умолчанию

Можно указать диск по умолчанию в конфигурационном файле `config/filament-s3-multipart-upload.php`:

```php
return [
    'prefix' => '_multipart-upload',
    'default_disk' => 'minio', // изменение диска по умолчанию
];
```

### Интерфейс предпросмотра файлов

Компонент включает улучшенный интерфейс предпросмотра с возможностью просмотра и удаления загруженных файлов:

- **Просмотр**: кнопка открывает файл для просмотра в новом окне
- **Удаление**: кнопка удаляет файл из хранилища, требуя подтверждения

## Конфигурация Laravel для работы с S3

Для корректной работы компонента убедитесь в правильной настройке диска S3 в файле `config/filesystems.php`:

```php
's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'url' => env('AWS_URL'),
    'endpoint' => env('AWS_ENDPOINT'),
    'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
    'throw' => false,
],
```

## Поддержка JavaScript для просмотра файлов

Для правильной работы функциональности просмотра файлов рекомендуется добавить в главный JavaScript файл следующую конфигурацию:

```javascript
window.Laravel = {
    fileStorage: {
        's3': {
            url: 'https://your-s3-bucket-url'
        },
        'minio': {
            url: 'https://your-minio-url'
        }
    }
};
```

## Пример полного использования

```php
FileUpload::make('attachment')
    ->disk('minio') // используем minio вместо S3
    ->directory(function ($record) {
        // Динамический путь на основе ID записи
        $uuid = $record ? $record->uuid : Str::uuid();
        return "documents/{$uuid}";
    })
    ->maxFileSize(1024 * 1024 * 1024) // 1GB
    ->multiple()
    ->maxNumberOfFiles(3);
```
