{{--
name: 'livewire_blog_post',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div>
    <x-slot:head>
        <meta name="description" content="{{ $post->meta_description }}">
        <meta name="robots" content="{{ $post->robot_index  ?? 'noindex' }}, {{ $post->robot_follow  ?? 'nofollow' }}">
    </x-slot:head>
    <div class="w-full">
        <!-- Bild -->
        @if($post->image)
        <div class="bg-white shadow-none border-gray-200 border rounded-lg overflow-hidden transition hover:shadow-md flex flex-col md:flex-row mb-6">
            <div class="w-full h-64 md:h-80 lg:h-96 xl:h-110 bg-gray-100 flex items-center justify-center">
                <img src="{{ $post->image->getThumbnailUrl(1200, 800, 80) }}"
                     alt="{{ $post->name }}"
                     title="{{ $post->name }}"
                     class="h-full w-full object-cover">
            </div>
        </div>
        @endif

        <flux:heading level="1" size="xl" class="my-6 text-3xl">{{ $post->name }}</flux:heading>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="col-span-1 lg:col-span-8 xl:col-span-9 border border-gray-200 rounded-lg p-4">
                {!! $post->content !!}
            </div>
            <div class="col-span-1 lg:col-span-4 xl:col-span-3 border border-gray-200 rounded-lg p-4">
                <div class="lg:sticky lg:top-10 !text-sm pt-4">

                    <!-- User -->
                    @if($post->user)
                    <div class="flex items-center mb-6">
                        <flux:icon.pencil-square variant="mini" class="mr-2 text-primary"/>
                        <div class="tex-sm">
                            {{ $post->user->name }}
                        </div>
                    </div>
                    @endif

                    <!-- Created date -->
                    <div class="flex items-center mb-6">
                        <flux:icon.calendar variant="mini" class="mr-2 text-primary"/>
                        <div class="tex-sm">
                            {{ $post->published_at?->translatedFormat('d. F Y') ?? $post->created_at->translatedFormat('d. F Y') }}
                        </div>
                    </div>

                    <!-- Category -->
                    @if($post->category)
                    <div class="flex items-center mb-6">
                        <flux:icon.folder variant="mini" class="mr-2 text-primary"/>
                        <div>
                            <flux:link href="{{ $this->getCategoryUrl() }}">{{ $post->category->name }}</flux:link>
                        </div>
                    </div>
                    @endif

                    <!-- Share -->
                    <div class="flex items-center mb-2">
                        <flux:icon.share variant="mini" class="mr-2 text-primary"/>
                        <div>
                            Teilen Sie diesen Beitrag auf:
                        </div>
                    </div>
                    <div class="flex">
                        <a
                            href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                           class="ml-8 text-[#1877F2] transition hover:opacity-70"
                           target="_blank"
                           rel="noopener noreferrer"
                        >
                            <x-bi-facebook/>
                        </a>
{{--                        <a href="#" class="ml-2 text-pink-500 transition hover:opacity-70"><x-bi-instagram /></a>--}}
                        <a
                            href="https://wa.me/?text={{ urlencode($post->name . ' ' . url()->current()) }}"
                            class="ml-2 text-[#25D366] transition hover:opacity-70"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <x-bi-whatsapp />
                        </a>
                        <a
                            href="https://www.linkedin.com/shareArticle?url={{ urlencode(url()->current()) }}"
                            class="ml-2 text-[#0077B5] transition hover:opacity-70"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <x-bi-linkedin />
                        </a>
                        <a
                            href="https://t.me/share/url?url={{ urlencode(url()->current()) }}&text={{ urlencode($post->name) }}"
                            class="ml-2 text-[#0088CC] transition hover:opacity-70"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <x-bi-telegram />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
