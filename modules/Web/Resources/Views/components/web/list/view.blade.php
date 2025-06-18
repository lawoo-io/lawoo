{{--
name: 'list_view',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div class="list-view-container">

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

    <!-- Table Section -->
    <flux:table class="border-t dark:border-t-gray-600 mb-2">
        <!-- Table Header -->
        <flux:table.columns>
            @isset($columnts)
                {{ $columnts }}
            @else
            <flux:table.column>
                {{ __t("No columns defined. Use the ‘columns’ slot.", "Web") }}
            </flux:table.column>
            @endif
        </flux:table.columns>

        <!-- Table Rows -->
        <flux:table.rows>
            @isset($body)
                {{ $body }}
            @else
            <flux:table.row>
                <flux:table.cell colspan="100">
                    <div class="text-center py-8 text-zinc-500">
                        {{ __t("No content defined. Use the ‘body’ slot.", "Web") }}
                    </div>
                </flux:table.cell>
            </flux:table.row>
            @endif
        </flux:table.rows>
    </flux:table>
    <!-- Empty State -->
    @isset($notFoundMessage)
    <div class="empty-state">
        <flux:card>
            <div class="text-center py-12">
                <flux:icon.document-magnifying-glass variant="outline" class="mx-auto h-12 w-12 text-zinc-400" />

                <flux:heading size="lg" class="mt-4">
                    {{ $notFoundMessage }}
                </flux:heading>
            </div>
        </flux:card>
    </div>
    @endif

    <!-- Footer Section with Pagination -->
    <div class="toolbar-right flex justify-end">
        @isset($footer)
            {{ $footer }}
        @endisset
    </div>
</div>
