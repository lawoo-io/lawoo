{{--
name: 'livewire_avatar_widget',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div>
    <flux:file-upload wire:model="file">
        <!-- Custom avatar uploader -->
        <div class="
        group relative flex items-center justify-center size-20 rounded-full transition-colors cursor-pointer
        border border-zinc-200 dark:border-white/10 hover:border-zinc-300 dark:hover:border-white/10
        bg-zinc-100 hover:bg-zinc-200 dark:bg-white/10 hover:dark:bg-white/15 in-data-dragging:dark:bg-white/15
    ">
            <!-- Show the uploaded file if it exists -->
            @if ($avatar && $avatar->isImage())
                <img src="{{ $avatar->getThumbnailUrl($permissionForShow, 200, 200, 80) }}" class="size-full object-cover rounded-full" />
            @else
                <!-- Show the default icon if no file is uploaded -->
                <flux:icon name="user" variant="solid" class="text-zinc-500 dark:text-zinc-400" />
            @endif

            <!-- Corner upload icon -->
            <div class="absolute bottom-0 right-0 bg-white dark:bg-zinc-800 rounded-full">
                <flux:icon name="arrow-up-circle" variant="solid" class="text-zinc-500 dark:text-zinc-400" />
            </div>

            <!-- Remove button -->
            @if($avatar)
            <div
                class="absolute top-0 right-0 transition-all duration-200 ease-out opacity-0 group-hover:opacity-100 bg-white dark:bg-zinc-800 rounded-full"
                x-on:click.stop="$wire.removeFile({{ $avatar->id }})"
            >
                <flux:icon name="x-circle" variant="solid" class="text-red-500" />
            </div>
            @endif
        </div>
    </flux:file-upload>
</div>
