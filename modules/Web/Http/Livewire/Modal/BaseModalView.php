<?php

namespace Modules\Web\Http\Livewire\Modal;

use Livewire\Attributes\On;
use Livewire\Component;

class BaseModalView extends Component
{

    public bool $show = false;

    public $content = '';

    #[On('open-modal')]
    public function openModal($content): void
    {
        $this->show = true;
        $this->content = $content;
    }

    public function close(): void
    {
        $this->show = false;
        $this->reset(['content']);
    }

    public function render()
    {
        return view('livewire.web.modal.view');
    }
}
