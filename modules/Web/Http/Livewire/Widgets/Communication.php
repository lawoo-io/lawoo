<?php

namespace Modules\Web\Http\Livewire\Widgets;

use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Modules\Web\Models\CommunicationType;

class Communication extends Component
{
    public Model $model;

    public string $field = '';

    public array $types = [];

    public $records;

    public function mount(Model $model, string $field, array $options = []): void
    {
        $this->model = $model;
        $this->records = $model->$field();

        $this->types = CommunicationType::active()->toArray();
    }

    public function render()
    {
        return view('livewire.web.widgets.communication.index');
    }
}
