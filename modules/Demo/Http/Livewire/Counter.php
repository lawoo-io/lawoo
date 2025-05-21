<?php

namespace Modules\Demo\Http\Livewire;


use Modules\Web\Http\Livewire\Counter as BaseWebCounter;

class Counter extends BaseWebCounter
{
    function increment()
    {
        $this->count = $this->count + 2;
    }
}
