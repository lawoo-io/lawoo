{{--
name: 'kanban_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div class="kanban-view-container">

    <!-- Toolbar Section -->
    <div class="toolbar-section mb-6">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
            <!-- Left Toolbar -->
            <div class="toolbar-left flex items-center gap-4">
                @isset($toolbar)
                    {{ $toolbar }}
                @endisset

                @isset($bulkActions)
                    <div class="bulk-actions">
                        {{ $bulkActions }}
                    </div>
                @endisset
            </div>

            <!-- Center Search -->
            <div class="toolbar-center flex-shrink-0">
                @isset($toolbarCenter)
                    {{ $toolbarCenter }}
                @endisset
            </div>

            <!-- Right Toolbar -->
            @isset($actions)
                <div class="toolbar-right flex items-center">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    </div>

    <div class="w-full border-t dark:border-t-gray-600 pt-4">
        {{ $slot }}
    </div>

    <div class="border-t dark:border-t-gray-600 mt-4 pt-4 flex items-center justify-end">
        @isset($footer)
            {{ $footer }}
        @endisset
    </div>
</div>
