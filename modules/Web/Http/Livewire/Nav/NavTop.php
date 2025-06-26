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

        $cacheTags = ['table:navigations'];
        $cacheKey = 'livewire:nav_top_navigation_level_1.'.$currentRoute;

        $currentNav = Cache::tags($cacheTags)->remember($cacheKey, now()->addDay(), function () use ($currentRoute) {
            $nav = Navigation::where('route', $currentRoute)->first();

            if (!$nav) {
                $routeRaw = explode('.', $currentRoute);
                $route = false;
                if (count($routeRaw) > 4) {
                    $route = $routeRaw[0] . '.' . $routeRaw[1] . '.' . $routeRaw[2] . '.' . $routeRaw[3];
                } elseif(count($routeRaw) > 3) {
                    $route = $routeRaw[0] . '.' . $routeRaw[1] . '.' . $routeRaw[2];
                }
                if($route) {
                    $nav = Navigation::where('route', $route)->first();
                }
            }

            if ($nav) {
                return $nav->getMainNavigation();
            }

            return null;
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
