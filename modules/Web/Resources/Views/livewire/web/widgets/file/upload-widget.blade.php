{{--
name: 'livewire_file_upload_widget',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div class="space-y-4" x-data="{ showPdfPreview: false, fileUrl: '', fileTitle: '' }">

    {{-- Flux File Input --}}
    <flux:file-upload
        wire:key="{{ $componentId . '-' . $uploaderKey }}"
        wire:model="{{ $multiple ? 'files' : 'file' }}"
        :multiple="$multiple"
        :label="$label"
        :name="$multiple ? 'files' : 'file'"
    >
        <flux:file-upload.dropzone
            heading="{{ __('messages.drop_browse', ['type' => __($multiple ? 'messages.files' : 'messages.file')]) }}"
            text="{{ $validationDescription }}"
            with-progress
            inline
        />
    </flux:file-upload>

    @if ($existingFiles && count($existingFiles))
    <div class="mt-3 flex flex-col gap-2">
        @foreach ($existingFiles as $file)
            <flux:file-item
                wire:key="{{ $file->id }}"
                heading="{{ $file->file_name }}"
                size="{{ $file->file_size }}"
                :image="$file->isImage() ? $file->getThumbnailUrl($permissionForShow, 80, 80, 80) : false"
            >
                <x-slot name="actions">

                    <!-- Set to Public -->
                    @if($setToPublic)
                        <flux:tooltip content="{{ __t('Publish or Unpublish a file', 'Web') }}">
                            <flux:switch
                                wire:change="changeState({{ $file }})"
                                :checked="$file->is_public ?? false"
                            />
                        </flux:tooltip>
                    @endif

                    <!-- Preview -->
                    @if($showPreview)
                    <flux:button
                        tooltip="{{ __t('Preview', 'Web') }}"
                        variant="subtle"
                        size="sm"
                        class="cursor-pointer"
                        x-on:click="showPdfPreview = true; fileUrl = '{{ $file->getEmbedUrl($permissionForShow) }}'; fileTitle = '{{ $file->file_name }}'">
                        <flux:icon.viewfinder-circle variant="micro"/>
                    </flux:button>
                    @endif

                    <!-- Download -->
                    <flux:button
                        tooltip="{{ __t('Download', 'Web') }}"
                        href="{{ $file->getDownloadUrl($permissionForShow) }}"
                        target="_blank"
                        variant="subtle"
                        size="sm"
                    >
                        <flux:icon.arrow-down-tray variant="micro"/>
                    </flux:button>

                    <!-- Remove -->
                    @can($permissionForEdit)
                        <flux:tooltip content="{{ __t('Remove', 'Web') }}">
                            <flux:file-item.remove wire:click="removeFile({{ $file->id }})" />
                        </flux:tooltip>
                    @endcan
                </x-slot>
            </flux:file-item>
        @endforeach
    </div>
    @endif

    {{-- Preview Modal --}}
    @if ($showPreview)
        <div x-show="showPdfPreview"
             x-cloak
             @keydown.escape="showPdfPreview = false, fileUrl = '', fileTitle = ''"
             @click="showPdfPreview = false, fileUrl = '', fileTitle = ''"
             class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4">

            <div class="bg-white rounded-lg w-full max-w-6xl h-full max-h-[98vh] overflow-hidden flex flex-col">
                {{-- Header --}}
                <div class="flex items-center justify-between p-4 border-b">
                    <div>
                        <h3 x-text="fileTitle" />
                    </div>
                    <div class="flex gap-2 ml-auto space-x-2">
{{--                        <a :href="fileUrl" target="_blank" class="cursor-pointer" wire:navigate="false">--}}
{{--                            <flux:tooltip content="{{ __t('Open in new Window', 'Web') }}">--}}
{{--                                <flux:icon.window variant="mini" class="size-5"/>--}}
{{--                            </flux:tooltip>--}}
{{--                        </a>--}}
                        <a x-on:click="showPdfPreview = false, fileUrl = '', fileTitle = ''" class="cursor-pointer">
                            <flux:tooltip content="{{ __t('Close', 'Web') }}">
                                <flux:icon.x-mark variant="mini" class="size-5"/>
                            </flux:tooltip>
                        </a>
                    </div>
                </div>

                {{-- PDF Viewer --}}
                <div class="flex-1">
                    <iframe :src="fileUrl" class="w-full h-full border-none"></iframe>
                </div>
            </div>
        </div>
    @endif
</div>
