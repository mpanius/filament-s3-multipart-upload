import { Uppy } from "@uppy/core"
import DragDrop from "@uppy/drag-drop"
import StatusBar from "@uppy/status-bar"
import AwsS3Multipart from "@uppy/aws-s3-multipart"

window.Uppy = Uppy
window.AwsS3Multipart = AwsS3Multipart
window.DragDrop = DragDrop
window.StatusBar = StatusBar

export default function uppy({state, maxFiles, maxSize, directory, companionUrl, csrfToken, disk}) {
    return {
        uppy: null,
        state,
        uploadedFiles: [],
        disk: disk || 's3',

        /**
         * Форматирует размер файла в человекочитаемый формат
         */
        bytesToSize(bytes) {
            const sizes = ["Bytes", "KB", "MB", "GB", "TB"]
            if (bytes === 0) return "n/a"
            const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)), 10)
            if (i === 0) return `${bytes} ${sizes[i]}`
            return `${(bytes / 1024 ** i).toFixed(1)} ${sizes[i]}`
        },

        /**
         * Открывает файл для просмотра в новом окне
         */
        viewFile(file) {
            const fileStorage = window.Laravel?.fileStorage || {};
            const rootUrl = fileStorage[this.disk]?.url || '';
            
            if (rootUrl) {
                const fileUrl = `${rootUrl}/${file.name}`;
                window.open(fileUrl, '_blank');
            } else {
                // Если URL не найден в конфигурации, можно построить URL для временного доступа
                // через signed URL или предоставить путь только для локальной разработки
                this.createSignedUrl(file).then(url => {
                    if (url) {
                        window.open(url, '_blank');
                    }
                }).catch(error => {
                    console.error('Ошибка при создании подписанного URL:', error);
                    alert('Не удалось открыть файл для просмотра');
                });
            }
        },

        /**
         * Создает подписанный URL для просмотра файла
         */
        async createSignedUrl(file) {
            try {
                const response = await fetch(`/api/s3-signed-url?key=${encodeURIComponent(file.name)}&disk=${this.disk}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-S3-Disk': this.disk
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    return data.url;
                }
                return null;
            } catch (error) {
                console.error('Ошибка при получении подписанного URL:', error);
                return null;
            }
        },

        /**
         * Удаляет файл из хранилища S3
         */
        async deleteFile(file) {
            if (!confirm('Вы уверены, что хотите удалить этот файл?')) {
                return;
            }
            
            try {
                const response = await fetch(`/api/s3-delete-file`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-S3-Disk': this.disk
                    },
                    body: JSON.stringify({
                        key: file.name
                    })
                });
                
                if (response.ok) {
                    // Удаляем файл из списка загруженных файлов
                    this.uploadedFiles = this.uploadedFiles.filter(f => f.name !== file.name);
                    // Обнуляем значение поля, если нет больше файлов
                    if (this.uploadedFiles.length === 0) {
                        this.state = '';
                    }
                    return true;
                } else {
                    alert('Не удалось удалить файл. Пожалуйста, попробуйте снова позже.');
                    return false;
                }
            } catch (error) {
                console.error('Ошибка при удалении файла:', error);
                alert('Произошла ошибка при удалении файла.');
                return false;
            }
        },

        init: function () {
            if (this.state) {
                this.uploadedFiles = [{ name: this.state, type: null, size: null }]
            }

            this.uppy = new Uppy({
                id: 'uppy',
                debug: true,
                restrictions: {
                    maxNumberOfFiles: maxFiles,
                    maxFileSize: maxSize,
                    minNumberOfFiles: 1
                },
                onBeforeUpload: (files) => {
                    const updatedFiles = {}

                    Object.keys(files).forEach(fileID => {
                        updatedFiles[fileID] = {
                            ...files[fileID],
                            name: `${directory}/${files[fileID].name}`,
                        }
                    })

                    return updatedFiles
                },
            });

            this.uppy
                .use(DragDrop, {
                    target: '.uppy__input',
                })
                .use(StatusBar, {
                    target: '.uppy__progress-bar',
                })
                .use(AwsS3Multipart, {
                    limit: 6,
                    companionUrl,
                    companionHeaders: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-S3-Disk': this.disk,
                    },
                })

            this.uppy.on("file-added", file => {
                uppy.upload && uppy.upload()
            });

            this.uppy.on("upload-success", (file, response) => {
                this.state = response.body.path

                if (maxFiles === 1) {
                    this.uploadedFiles = [file]
                } else {
                    this.uploadedFiles = [...this.uploadedFiles, file]
                }
            });
        },
    }
}
