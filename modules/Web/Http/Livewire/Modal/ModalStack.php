<?php

namespace Modules\Web\Http\Livewire\Modal;


use Livewire\Component;

class ModalStack extends Component
{
    public array $stack = [];

    public int $width;

    public function mount(): void
    {
        $this->width = 90;
    }

    protected $listeners = [
        'open-modal-stack' => 'open',
        'close-modal-stack' => 'close'
    ];

    public function open(string $componentName, int $id = null, string $url = '', string $type = 'edit', array $props = []): void
    {
        $this->stack[] = [
            'name' => $componentName,
            'id' => $id,
            'type' => $type,
            'props' => $props,
        ];

        $this->width = $this->width - 1;
        if (!empty($url)) {
            $this->dispatch('update-url', url: $url);
        }

        $this->dispatch('reinit-glightbox-delayed');
    }

    public function close()
    {
        array_pop($this->stack);
        $this->width = $this->width + 1;
        $this->dispatch('revert-url');
        $this->dispatch('reinit-glightbox-delayed');
    }

    public function render()
    {
        return view('livewire.web.modal.stack');
    }
}
