<?php

namespace Modules\Web\Http\Livewire\Nav;

use Livewire\Component;
use Modules\Core\Models\Navigation;

class NavTop extends Component
{
    public object $subNavList;
    public ?int $activeMainNavId = null;

    public function mount()
    {
        $this->loadNavigationFromCurrentRoute();
    }

    private function loadNavigationFromCurrentRoute()
    {
        $currentRoute = request()->route()->getName();

        $currentNav = Navigation::where('route', $currentRoute)->where('level', 0)->first();

        if($currentNav && $currentNav->level == 0) {
            $this->subNavList = $currentNav->children;
        }
    }

    public function render()
    {
        return view('livewire.web.nav.nav-top');
    }
}
