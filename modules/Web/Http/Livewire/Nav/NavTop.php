<?php

namespace Modules\Web\Http\Livewire\Nav;

use Illuminate\Support\Facades\Cache;
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

        $explodeRoute = explode('.', $currentRoute);

        $keyRoute = count($explodeRoute) > 2 ? $explodeRoute[0] . '.' . $explodeRoute[1] . '.' . $explodeRoute[2] : $explodeRoute[0];

        $cacheTags = ['table:navigations'];
        $cacheKey = 'livewire:nav_top_navigation_level_1.'.$keyRoute;

        $currentNav = Cache::tags($cacheTags)->remember($cacheKey, now()->addDay(), function () use ($keyRoute) {
            return Navigation::with('children')->where('route', $keyRoute)->where('level', 0)->first();
        });

        if($currentNav && $currentNav->level == 0) {
            $this->subNavList = $currentNav->children;
        }
    }

    public function render()
    {
        return view('livewire.web.nav.nav-top');
    }
}
