{{--
name: 'livewire_blog_posts',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div class="text-left">
    @if($category)
        <x-slot:head>
            <meta name="description" content="{{ $category->meta_description }}">
            <meta name="robots" content="{{ $category->robot_index ?? 'noindex' }}, {{ $category->robot_follow ?? 'nofollow' }}">
        </x-slot:head>
    @endif
    <div class="flex">
        <flux:heading size="xl" class="mb-4" level="1">{{ $category ? 'Blog: ' . $category->name : 'Blog' }}</flux:heading>
    </div>
    <div class="grid gap-6 relative">
        @foreach($posts as $post)


            <!-- only first post -->
            @if($loop->first)
                <div class="grid grid-cols-12 gap-6 lg:mb-10">
            @endif
            <!-- end only first post -->

            <!-- only for 2-4 post -->
            @if($loop->index === 1)
                <div class="grid grid-cols-12 gap-6 lg:mb-10">
            @endif
            <!-- end only for 2-4 post -->

            <!-- for > 5 posts -->
            @if($loop->index === 4)
                <div class="grid grid-cols-12 gap-6 mb-4">
                    @if($categories)
                    <!-- Navigation Column -->
                    <div
                        x-data="{ open: false }"
                        class="col-span-12 lg:col-span-3 absolute lg:sticky lg:block p-4 -top-14 right-0 lg:right-auto h-fit lg:border border-gray-200 rounded-lg z-50 pr-0 lg:pr-4"
                    >

                        <!-- Hamburger (nur Mobile sichtbar) -->
                        <div class="flex cursor-pointer lg:cursor-default lg:hidden" @click="open = !open">
                           <span class="text-lg mr-2">Kategorien</span> <flux:icon.bars-2 class="pr-0" />
                        </div>

                        <!-- Navigation -->
                        <flux:navbar class="w-full">
                            <nav
                                class="fixed lg:static inset-y-0 left-0 w-64 lg:w-full bg-white lg:bg-transparent shadow-lg lg:shadow-none z-55 transform transition-transform duration-300 ease-in-out p-4 lg:p-0"
                                :class="{ '-translate-x-full': !open, 'translate-x-0': open, 'lg:translate-x-0': true }"
                            >
                                <div class="flex items-center mb-2">
                                    <flux:icon.bars-2 class="mr-2" />
                                    <span class="text-lg">Kategorien</span>
                                </div>
                                @foreach($categories as $category)
                                    <flux:navbar.item class="block w-full" href="{{ $this->getCategoryUrl($category->slug) }}" wire:navigate>{{ $category->name }}</flux:navbar.item>
                                @endforeach
                            </nav>
                        </flux:navbar>

                        <!-- Overlay (nur Mobile sichtbar) -->
                        <div
                            x-show="open"
                            class="fixed inset-0 bg-black opacity-40 z-30 lg:hidden"
                            @click="open = false"
                        ></div>
                    </div>
                    @endif

                <!-- Content Column -->
                <div class="col-span-12 lg:col-span-9 grid grid-cols-12 h-full items-stretch gap-6">
            @endif
            <!-- end for > 5 posts -->


            <div class="col-span-12 @if($loop->index > 0 && $loop->index < 4) lg:col-span-4 @endif">
                <div class="bg-white shadow-none border-gray-200 border rounded-lg overflow-hidden @if($loop->first) lg:flex @elseif($loop->index > 3) md:flex @endif transition hover:shadow-md">
                    <!-- Bild-Container -->
                    <a
                        href="{{ $blogPage }}/{{ $post->slug }}"
                        class="w-full @if($loop->first) lg:w-2/3 h-48 md:h-88 @elseif($loop->index > 0 && $loop->index < 4) h-48 md:h-72 lg:h-48 @elseif($loop->index > 3) md:w-1/4 h-48 @endif bg-gray-100 flex items-center justify-center"
                        wire:navigate
                    >
                        @if($post->image)
                            <img src="{{ $post->image->getThumbnailUrl(1200, 800, 80) }}"
                                 alt="{{ $post->name }}"
                                 title="{{ $post->name }}"
                                 class="h-full w-full object-cover">
                        @else
                            <span class="text-gray-400 text-sm">Kein Bild</span>
                        @endif
                    </a>

                    <!-- Inhalt -->
                    <div class="p-6 flex flex-col flex-1">
                        <h2 class="text-xl mb-2 mt-1">
                            <flux:link href="{{ $blogPage }}/{{ $post->slug }}" variant="ghost" wire:navigate>
                                {{ $post->name }}
                            </flux:link>
                        </h2>

                        <p class="text-gray-600 mb-4 flex-1">
                            {{ Str::limit($post->short_description, 150) }}
                        </p>

                        <div class="text-sm text-gray-500 mt-auto flex justify-between">
                            <div>
                                {{ $post->published_at?->translatedFormat('d. F Y') ?? $post->created_at->translatedFormat('d. F Y') }}
                            </div>
                            <div>
                                @if($post->category)
                                    <flux:link href="{{ $this->getCategoryUrl($post->category->slug) }}" variant="ghost" wire:navigate>
                                        {{ $post->category->name }}
                                    </flux:link>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- only first post -->
            @if($loop->first)
                </div>
            @endif
            <!-- end only first post -->

            <!-- only for 2-4 post -->
            @if($loop->index > 0 && $loop->index < 4)
                @if($loop->last || $loop->index === 3)
                    </div>
                @endif
            @endif
            <!-- end only for 2-4 post -->

            <!-- only for > 5 posts -->
            @if($loop->index > 3)
                @if($loop->last)
                    </div>
                </div>
                @endif
            @endif
            <!-- end only for > 5 posts -->


        @endforeach
    </div>
    @if($posts->hasPages())
        <div class="mt-4">
            <flux:pagination :paginator="$posts" />
        </div>
    @endif
</div>
