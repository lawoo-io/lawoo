<?php

namespace Modules\Core\Abstracts;

use Livewire\Component;

abstract class BaseComponent extends Component
{
    public function notify(string $message, string $type = 'success')
    {
        $this->dispatchBrowserEvent('notification', [
            'type' => $type,
            'message' => $message,
        ]);
    }
}
