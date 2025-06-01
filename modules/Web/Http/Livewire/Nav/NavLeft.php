<?php

namespace Modules\Web\Http\Livewire\Nav;


use Livewire\Component;
use Modules\Core\Models\Navigation;

class NavLeft extends Component
{
    public object $navlist;
    public ?int $activeMainNavId = null;

    public function mount()
    {
        $this->navlist = Navigation::query()->mainNavigation()->active()->ordered()->get();
    }

    public function render()
    {
        return view('livewire.web.nav.nav-left');
    }
}
