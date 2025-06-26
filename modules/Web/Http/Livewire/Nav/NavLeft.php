<?php

namespace Modules\Web\Http\Livewire\Nav;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Modules\Core\Models\Navigation;

class NavLeft extends Component
{
    public object $navlist;

    public ?int $activeMainNavId = null;

    public function mount()
    {
        $cacheTags = ['table:navigations'];
        $cacheKey = 'livewire:nav_left_navigation_level_0';
        $this->navlist = Cache::tags($cacheTags)->remember($cacheKey, now()->addDay(), function () {
            return Navigation::with('children')->mainNavigation()->active()->ordered()->get();
        });
    }

    public function render()
    {
        return view('livewire.web.nav.nav-left');
    }
}
