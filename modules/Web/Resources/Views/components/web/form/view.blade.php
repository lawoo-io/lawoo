{{--
name: 'from_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div class="form-view-container">
    <!-- Toolbar section -->
    <div class="toolbar-section mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 lg:gap-4 lg:items-center">
            @isset($toolbar)
                <div class="toolbar-left flex items-center gap-4 col-span-2 lg:col-span-1">
                    {{ $toolbar }}
                </div>
            @endisset

            <!-- Buttons -->
            <div class="toolbar-center col-span-1 md:col-span-1 md:justify-start pt-2 lg:pt-0 lg:col-span-2 flex  lg:justify-center">
                @isset($toolbarCenter)
                    {{ $toolbarCenter }}
                @endisset
            </div>

            <!-- Right Toolbar -->
            @isset($actions)
                <div class="toolbar-right flex items-center col-span-1 justify-end">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
        {{ $slot }}
    </div>
</div>
