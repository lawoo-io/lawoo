{{--
name: 'livewire_file_uploader',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div class="file-uploader" x-data="{ showPdfPreview: false, pdfUrl: '', pdfTitle: '' }">
    @if ($this->mode === 'image')
        <div class="flex items-start gap-4"
             x-data="{ uploading: false, progress: 0 }"
             x-on:livewire-upload-start="uploading = true"
             x-on:livewire-upload-finish="uploading = false"
             x-on:livewire-upload-cancel="uploading = false"
             x-on:livewire-upload-error="uploading = false"
             x-on:livewire-upload-progress="progress = $event.detail.progress"
        >
            <div x-show="uploading" class="text-sm text-gray-500 mt-auto">{{ __t('Uploaded', 'Web') }}... <span x-text="progress + '%'"></span></div>
            {{-- Right: Upload Button/Preview --}}
            <div class="ml-auto">
                <div class="relative group">
                    {{-- Hidden File Input --}}
                    <input type="file"
                           wire:model="file"
                           @if($this->multiple) multiple @endif
                           @if($accept !== '*') accept="{{ $accept }}" @endif
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                           id="file-input-{{ $field }}">

                    {{-- Visual Upload Button/Preview --}}
                    <div class="{{ $this->imageClass }} {{ !$this->file && !$this->existingFile ? 'border border-dashed border-gray-300' : '' }} rounded-lg flex items-center justify-center cursor-pointer hover:bg-blue-50 transition-colors overflow-hidden">
                        @if ($this->file)
                            <img src="{{ $this->file->temporaryUrl() }}" class="w-full h-full object-cover" />
                        @elseif($this->existingFile)
                            <img src="{{ $this->getThumb($this->existingFile) }}" class="w-full h-full object-cover" />
                            @if ($this->glightbox)
                                <a href="{{ $this->getThumb($this->existingFile, 1000, 1000, 80) }}" data-type="image" class="absolute top-1 left-1 opacity-0 group-hover:opacity-70 hover:opacity-100 transition-opacity duration-100 z-30 glightbox">
                                    <flux:tooltip content="{{ __t('Open', 'Web') }}">
                                        <flux:icon.magnifying-glass class="size-4 text-white" variant="micro"/>
                                    </flux:tooltip>
                                </a>
                            @endif
                            @can($this->permissionForEdit)
                                <flux:tooltip content="{{ __t('Remove', 'Web') }}">
                                    <button type="button" class="absolute top-1 right-1 opacity-0 group-hover:opacity-70 hover:opacity-100 transition-opacity duration-100 z-30 cursor-pointer" wire:click="removeFile({{ $this->existingFile->id }})">
                                        <flux:icon.x-circle class="size-4 text-gray-200 shadow shadow-gray-800 rounded-4xl" variant="micro"/>
                                    </button>
                                </flux:tooltip>
                            @endcan
                        @else
                            <flux:icon.plus class="size-10 text-gray-300" />
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @elseif($this->mode === 'images')
        <div class="flex items-start gap-4"
             x-data="{ uploading: false, progress: 0 }"
             x-on:livewire-upload-start="uploading = true"
             x-on:livewire-upload-finish="uploading = false"
             x-on:livewire-upload-cancel="uploading = false"
             x-on:livewire-upload-error="uploading = false"
             x-on:livewire-upload-progress="progress = $event.detail.progress"
        >

            <div class="ml-auto flex gap-1">
                <div class="relative group">
                    <input type="file"
                           wire:model="files"
                           @if($this->multiple) multiple @endif
                           @if($accept !== '*') accept="{{ $accept }}" @endif
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                           id="file-input-{{ $field }}"/>
                    <div class="{{ $this->imageClass }} border border-dashed border-gray-300 rounded-lg flex items-center justify-center cursor-pointer hover:bg-blue-50 transition-colors overflow-hidden">
                        <flux:icon.plus x-show="!uploading" class="size-10 text-gray-300" />
                        <div x-show="uploading" class="text-sm text-gray-500 text-center"><br><span x-text="progress + '%'"></span></div>
                    </div>
                </div>
                @foreach($this->existingFiles as $file)
                    <div class="relative group" wire:key="existing-{{ $file->id }}">
                        <div class="{{ $this->imageClass }} rounded-lg flex items-center justify-center cursor-pointer hover:bg-blue-50 transition-colors overflow-hidden">
                            <a @if($this->glightbox) href="{{ $this->getThumb($file, 1000, 1000, 80) }}" data-type="image" @endif class="{{ $this->glightbox ? 'glightbox': '' }}">
                                <img src="{{ $this->getThumb($file) }}"/>
                            </a>
                            @can($this->permissionForEdit)
                                <flux:tooltip content="{{ __t('Remove', 'Web') }}">
                                    <button type="button" class="absolute top-1 right-1 opacity-0 group-hover:opacity-70 hover:opacity-100 transition-opacity duration-100 z-30 cursor-pointer" wire:click="removeFile({{ $file->id }})">
                                        <flux:icon.x-circle class="size-4 text-gray-200 shadow shadow-gray-800 rounded-4xl" variant="micro"/>
                                    </button>
                                </flux:tooltip>
                            @endcan
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif($this->mode === 'document')
        <div class=""
             x-data="{ uploading: false, progress: 0 }"
             x-on:livewire-upload-start="uploading = true"
             x-on:livewire-upload-finish="uploading = false"
             x-on:livewire-upload-cancel="uploading = false"
             x-on:livewire-upload-error="uploading = false"
             x-on:livewire-upload-progress="progress = $event.detail.progress"
        >
            @if ($this->label)
                <flux:label class="block pb-[6px]">{{ $this->label }}</flux:label>
            @endif
            @if ($this->existingFile)
                <div class="flex items-center gap-1 justify-between py-2.5 px-3.5 bg-white border border-gray-200 rounded-lg shadow-xs text-sm ">
                    <div class="flex gap-1 opacity-80">
                        <flux:tooltip content="{{ __t('Size:', 'Web') }} {{ $this->existingFile->getHumanFileSizeAttribute() }}">
                            <flux:icon.document-text class="size-4.5" />
                        </flux:tooltip>
                        <span>{{ $this->existingFile->file_name }}</span>
                    </div>
                    <div class="flex gap-1">
                        <flux:tooltip content="{{ __t('Open', 'Web') }}">
                            <div  x-on:click="showPdfPreview = true; pdfUrl = '{{ $this->existingFile->getEmbedUrl($permissionForShow) }}'">
                                <flux:icon.viewfinder-circle variant="mini" class="size-4 cursor-pointer text-gray-500 hover:text-gray-800"/>
                            </div>
                        </flux:tooltip>
                        <flux:tooltip content="{{ __t('Download', 'Web') }}">
                            <flux:link href="{{ $this->getDownloadUrl($this->existingFile->id) }}">
                                <flux:icon.arrow-down-tray variant="mini" class="size-4 cursor-pointer text-gray-500 hover:text-gray-800"/>
                            </flux:link>
                        </flux:button>
                        <flux:tooltip content="{{ __t('Remove', 'Web') }}">
                            <flux:icon.x-mark wire:click="removeFile({{ $this->existingFile->id }})" variant="mini" class="size-4 cursor-pointer text-gray-500 hover:text-gray-800"/>
                        </flux:tooltip>
                    </div>
                </div>
            @else
                <label for="fileInput" class="flex items-start gap-1 border py-2.5 px-3.5 border-dashed border-gray-300 rounded-lg hover:border-gray-400 transition-colors cursor-pointer group text-sm">
                    <input wire:model="file" type="file" id="fileInput" class="hidden" @if($accept !== '*')accept="{{ $accept }}" @endif>
                    <div class="w-full flex justify-between items-center">
                        <span x-show="!uploading" class="ml-1">Dateien durchsuchen</span>
                        <div x-show="uploading" class="text-sm text-gray-500 mt-auto">{{ __t('Uploaded', 'Web') }}... <span x-text="progress + '%'"></span></div>
                        <flux:tooltip content="Durchsuchen">
                            <flux:icon.arrow-up-tray variant="mini" class="size-4 cursor-pointer"/>
                        </flux:tooltip>
                    </div>
                </label>
            @endif
        </div>
    @elseif($this->mode === 'documents')
        <div class=""
             x-data="{ uploading: false, progress: 0 }"
             x-on:livewire-upload-start="uploading = true"
             x-on:livewire-upload-finish="uploading = false"
             x-on:livewire-upload-cancel="uploading = false"
             x-on:livewire-upload-error="uploading = false"
             x-on:livewire-upload-progress="progress = $event.detail.progress"
        >
            @if ($this->label)
                <flux:label class="block pb-[6px]">{{ $this->label }}</flux:label>
            @endif
            <div class="py-2.5 px-3.5 bg-white border border-gray-200 rounded-lg shadow-xs text-sm ">
                <div class="flex items-center gap-1 justify-between">
                    <div x-show="!uploading" class="opacity-80">
                        @if (count($this->existingFiles) < 1)
                            Dateien durchsuchen
                        @else
                            {{ count($this->existingFiles) }} Dateien hochgeladen
                        @endif
                    </div>
                    <div x-show="uploading" class="text-sm text-gray-500 mt-auto">{{ __t('Uploaded', 'Web') }}... <span x-text="progress + '%'"></span></div>
                    <div class="">
                        <label for="{{ $this->field }}">
                            <input wire:model="files" type="file" class="hidden" id="{{ $this->field }}" @if($accept !== '*')accept="{{ $accept }}" @endif multiple/>
                            <flux:tooltip content="Durchsuchen">
                                <flux:icon.arrow-up-tray variant="mini" class="size-4 cursor-pointer"/>
                            </flux:tooltip>
                        </label>
                    </div>
                </div>
                @foreach($this->existingFiles as $file)
                    <div class="flex items-center gap-1 justify-between pt-2 mt-2 border-t">
                        <div class="flex gap-1 opacity-80">
                            <flux:tooltip content="{{ __t('Size:', 'Web') }} {{ $file->getHumanFileSizeAttribute() }}">
                                <flux:icon.document-text class="size-4.5" />
                            </flux:tooltip>
                            <span>{{ $file->file_name }}</span>
                        </div>
                        <div class="flex gap-1">
                            <flux:tooltip content="{{ __t('Open', 'Web') }}">
                                <div  x-on:click="showPdfPreview = true; pdfUrl = '{{ $file->getEmbedUrl($permissionForShow) }}'">
                                    <flux:icon.viewfinder-circle variant="mini" class="size-4 cursor-pointer text-gray-500 hover:text-gray-800"/>
                                </div>
                            </flux:tooltip>
                            <flux:tooltip content="{{ __t('Download', 'Web') }}">
                                <flux:link href="{{ $this->getDownloadUrl($file->id) }}">
                                    <flux:icon.arrow-down-tray variant="mini" class="size-4 cursor-pointer text-gray-500 hover:text-gray-800"/>
                                </flux:link>
                            </flux:button>
                            <flux:tooltip content="{{ __t('Remove', 'Web') }}">
                                <flux:icon.x-mark wire:click="removeFile({{ $file->id }})" variant="mini" class="size-4 cursor-pointer text-gray-500 hover:text-gray-800"/>
                            </flux:tooltip>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- PDF Preview Modal --}}
    @if ($this->mode === 'document' || $this->mode === 'documents')
        <div x-show="showPdfPreview"
             x-cloak
             @keydown.escape="showPdfPreview = false, pdfUrl = ''"
             @click="showPdfPreview = false, pdfUrl = ''"
             class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4">

            <div class="bg-white rounded-lg w-full max-w-6xl h-full max-h-[98vh] overflow-hidden flex flex-col">
                {{-- Header --}}
                <div class="flex items-center justify-between p-4 border-b">
                    <div class="flex gap-2 ml-auto space-x-2">
                        <a :href="pdfUrl" target="_blank" class="cursor-pointer">
                            <flux:tooltip content="{{ __t('Open in new Window', 'Web') }}">
                                <flux:icon.window variant="mini" class="size-5"/>
                            </flux:tooltip>
                        </a>
                        <a x-on:click="showPdfPreview = false, pdfUrl = ''" class="cursor-pointer">
                            <flux:tooltip content="{{ __t('Close', 'Web') }}">
                                <flux:icon.x-mark variant="mini" class="size-5"/>
                            </flux:tooltip>
                        </a>
                    </div>
                </div>

                {{-- PDF Viewer --}}
                <div class="flex-1">
                    <iframe :src="pdfUrl" class="w-full h-full border-none"></iframe>
                </div>
            </div>
        </div>
    @endif
</div>
