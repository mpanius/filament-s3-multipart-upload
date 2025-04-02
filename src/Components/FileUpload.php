<?php

declare(strict_types=1);

namespace CloudMazing\FilamentS3MultipartUpload\Components;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Support\Concerns\HasExtraAlpineAttributes;

class FileUpload extends Field
{
    use HasExtraAlpineAttributes;
    
    protected string $view = 'filament-s3-multipart-upload::components.file-upload';

    protected int $maxFileSize = 5 * 1024 * 1024 * 1024; // 5GB

    protected int $maxNumberOfFiles = 10;

    protected bool $multiple = false;

    protected string|\Closure $directory = '';
    
    protected string|\Closure|null $disk = null;

    protected \Closure|bool $invisible = false;

    /**
     * Определяет, должен ли компонент быть скрытым на странице
     *
     * @param \Closure|bool $invisible
     * @return $this
     */
    public function invisible(\Closure|bool $invisible = true): self
    {
        $this->invisible = $invisible;

        return $this;
    }

    /**
     * Получает статус видимости компонента
     *
     * @return bool
     */
    public function getInvisible(): bool
    {
        return $this->evaluate($this->invisible);
    }

    /**
     * Устанавливает директорию для загрузки файлов
     *
     * @param string|\Closure $directory
     * @return $this
     */
    public function directory(string|\Closure $directory): self
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * Получает директорию для загрузки файлов
     *
     * @return string
     */
    public function getDirectory(): string
    {
        return $this->evaluate($this->directory);
    }
    
    /**
     * Устанавливает S3-совместимый диск для загрузки файлов
     *
     * @param string|\Closure|null $disk
     * @return $this
     */
    public function disk(string|\Closure|null $disk): self
    {
        $this->disk = $disk;
        
        return $this;
    }
    
    /**
     * Получает настроенный диск или диск по умолчанию
     *
     * @return string
     */
    public function getDisk(): string
    {
        $disk = $this->evaluate($this->disk);
        
        return $disk ?? config('filament-s3-multipart-upload.default_disk', 's3');
    }

    /**
     * Проверяет, настроен ли AWS для выбранного диска
     *
     * @return bool
     */
    public function hasAwsConfigured(): bool
    {
        $disk = $this->getDisk();
        
        return config("filesystems.disks.{$disk}.bucket")
            && config("filesystems.disks.{$disk}.key")
            && config("filesystems.disks.{$disk}.region")
            && config("filesystems.disks.{$disk}.secret");
    }

    /**
     * Получает URL для companion API
     *
     * @return string
     */
    public function companionUrl(): string
    {
        return '/'.config('filament-s3-multipart-upload.prefix');
    }

    /**
     * Получает максимальный размер файла
     *
     * @return int
     */
    public function getMaxFileSize(): int
    {
        return $this->maxFileSize;
    }

    /**
     * Устанавливает максимальный размер файла
     *
     * @param int $bytes
     * @return $this
     */
    public function maxFileSize(int $bytes): self
    {
        $this->maxFileSize = $bytes;

        return $this;
    }

    /**
     * Включает мульти-загрузку файлов
     *
     * @return $this
     */
    public function multiple(): self
    {
        $this->multiple = true;

        return $this;
    }

    /**
     * Проверяет, включена ли мульти-загрузка
     *
     * @return bool
     */
    public function getMultiple(): bool
    {
        return $this->multiple;
    }

    /**
     * Устанавливает максимальное количество файлов для загрузки
     *
     * @param int $maxNumberOfFiles
     * @return $this
     */
    public function maxNumberOfFiles(int $maxNumberOfFiles): self
    {
        $this->maxNumberOfFiles = $maxNumberOfFiles;

        return $this;
    }

    /**
     * Получает максимальное количество файлов для загрузки
     *
     * @return int
     */
    public function getMaxNumberOfFiles(): int
    {
        if (! $this->multiple) {
            return 1;
        }

        return $this->maxNumberOfFiles;
    }
    
    /**
     * Подготавливаем скриптовые данные для компонента в соответствии с Filament 3+
     *
     * @return array<string, mixed>
     */
    protected function getExtraAlpineAttributes(): array
    {
        return [
            'x-data' => 'uppy({
                state: $wire.entangle(\'' . $this->getStatePath() . '\'),
                maxFiles: ' . $this->getMaxNumberOfFiles() . ',
                maxSize: ' . $this->getMaxFileSize() . ',
                directory: \'' . $this->getDirectory() . '\',
                companionUrl: \'' . $this->companionUrl() . '\',
                csrfToken: \'' . csrf_token() . '\',
                disk: \'' . $this->getDisk() . '\'
            })',
        ];
    }
}
