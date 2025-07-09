{{--
name: 'livewire_form_messages',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<div>
    @foreach($messages as $message)
    <div class="p-4 rounded-lg border dark:border-gray-600 mb-2">
        <div class="flex flex-row sm:items-center gap-2">
            <div>
                <flux:avatar src="https://randomuser.me/api/portraits/men/1.jpg" size="xs" class="shrink-0" />
            </div>
            <div class="flex flex-col gap-0.5 sm:gap-2 sm:flex-row sm:items-center">
                <div class="flex items-center gap-2">
                    @isset($message['user']['name'])
                    <flux:heading>{{ $message['user']['name'] }}</flux:heading>
                    @endisset
                    @if ($message['message_type'] === 'audit')

                    @elseif($message['message_type'] === 'note')
                        <flux:icon name="pencil" class="text-gray-500 dark:text-gray-200 size-4.5"/>
                    @elseif ($message['message_type'] === 'system')

                    @elseif ($message['message_type'] === 'alert')
                        <flux:icon name="arrow-path-rounded-square" class="text-gray-500 dark:text-gray-200 size-4.5"/>
                    @elseif($message['message_type'] === 'email')
                        <flux:icon name="envelope" class="text-gray-500 dark:text-gray-200 size-4.5"/>
                    @endif
                    {{--                            <flux:badge color="lime" size="sm" icon="check-badge" inset="top bottom">Moderator</flux:badge>--}}
                </div>
                <flux:text class="text-sm">{{ $message['created_at']->diffForHumans() }}</flux:text>
            </div>
        </div>
        <div class="min-h-2 sm:min-h-1"></div>

        <div class="pl-8">
            <flux:text variant="strong">
                @if ($message['subject'])
                    <flux:heading level="2">{{ $message['subject'] }}</flux:heading>
                @endif
                {!! $message['body']  !!}
            </flux:text>
            <div class="min-h-2"></div>
        </div>
    </div>
    @endforeach

    @if($hasMore)
        <div x-intersect="$wire.loadMore()" class="p-4 text-center">
            <flux:text class="text-gray-500">Lade weitere Nachrichten...</flux:text>
        </div>
    @endif
</div>
