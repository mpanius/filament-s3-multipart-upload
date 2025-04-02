@assets
<link href="https://releases.transloadit.com/uppy/v2.3.0/uppy.min.css" rel="stylesheet">
@endassets

@php($id = 'uppy-' . Str::random(8))

@script
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Livewire.hook('morph.updated', ({component}) => {
            if (component.id === document.getElementById('uppy-{{ $id }}').getAttribute('wire:id')) {
                console.log('uppy updated');
            }
        })
    });
</script>
@endscript

<div class="uppy-component" @if($getInvisible()) style="display:none" @endif>
    <x-dynamic-component
        :component="$getFieldWrapperView()"
        :field="$field"
    >
        <div
            class="uppy"
            x-ignore
            ax-load
            ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('uppy', 'cloudmazing/filament-s3-multipart-upload') }}"
            x-data="uppy({
                state: $wire.entangle('{{ $getStatePath() }}'),
                maxFiles: {{ $getMaxNumberOfFiles() }},
                maxSize: {{ $getMaxFileSize() }},
                directory: '{{ $getDirectory() }}',
                companionUrl: '{{ $companionUrl() }}',
                csrfToken: '{{ csrf_token() }}',
                disk: '{{ $getDisk() }}'
            })"
            wire:key="{{ $id }}"
        >
            <div class="uppy__input">
            </div>

            <div class="uppy__progress-bar">
            </div>

            <div class="uppy__files mt-2">
                <template x-for="file in uploadedFiles" :key="file.name">
                    <div class="uppy__file file py-2 px-4 text-sm dark:text-white rounded-lg border mb-2">
                        <div class="flex justify-between items-center mb-1">
                            <span class="file__name font-bold text-sm" x-text="file.name"></span>
                        </div>

                        <div class="file__meta space-x-2 text-xs text-neutral-700 flex items-center justify-between">
                            <div>
                                <span x-show="file.type" class="file__size" x-text="bytesToSize(file.size)"></span>
                                <span x-show="file.type" class="file__type" x-text="file.type"></span>
                            </div>
                            <div class="flex space-x-2">
                                <button 
                                    type="button" 
                                    class="text-primary-600 hover:text-primary-500 transition-colors px-2 py-1 rounded-md text-xs flex items-center"
                                    x-on:click.prevent="viewFile(file)"
                                    title="Просмотреть файл"
                                >
                                    <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Просмотр
                                </button>
                                <button 
                                    type="button" 
                                    class="text-danger-600 hover:text-danger-500 transition-colors px-2 py-1 rounded-md text-xs flex items-center"
                                    x-on:click.prevent="deleteFile(file)"
                                    title="Удалить файл"
                                >
                                    <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Удалить
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            @unless($hasAwsConfigured())
                <p class="text-danger-500 text-sm mt-2">Не найдена конфигурация AWS S3 для диска «{{ $getDisk() }}».</p>
            @endunless
        </div>
    </x-dynamic-component>
</div>
