<?php

namespace Modules\Web\Http\Livewire\Form;

use Flux\Flux;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Validate;
use Livewire\Component;

class BaseFormView extends Component
{
    /**
     * @var string
     */
    public string $moduleName = '';

    /**
     * @var string
     */
    public string $className = '';

    /**
     * @Var string
     */
    protected $repositoryClass = '';

    /**
     * @var string
     */
    public string $view = 'livewire.web.form.base-form-view';

    /**
     * From URL
     * @var int
     */
    public int $id;

    /**
     * @var array
     */
    public array $fields = [];

    /**
     * @var array
     */
    public $rules = [];

    /**
     * @var array
     */
    public array $data = [];

    /**
     * @return bool
     */
    public bool $showRightContent = false;

    /**
     * @var string
     */
    public string $permissionForEdit = '';

    /**
     * @var string
     */
    public string $permissionForDeleting = '';

    /**
     * @var string
     */
    public string $recordsRoute = '';

    public function mount(): void
    {
        $this->id = request()->route('id');

        $this->loadData();

        $this->setFields();
        $this->setRules();
        $this->initializeFields();
    }

    protected function loadData(): void
    {
        $record = $this->resolveRepository()->find($this->id);
        $this->data = $record->attributesToArray();
    }

    public function setRules(): void
    {
        $this->rules = [];
    }

    public function save(): void
    {
        $result = false;
        if($this->rules) $this->validate($this->rules);

        if($this->id !== 'new') {
            $result = $this->update();
        }

        if($result){
            Flux::toast(text: __t('Updated successfully', 'Web'), variant: 'success');
        }
    }

    protected function update(): ?Model
    {
        if ($this->permissionForEdit !== '') {
            $this->resolveRepository()->authorize($this->permissionForEdit);
        }

        return $this->resolveRepository()->update($this->id, $this->data);
    }

    public function setFields(): void
    {
        $this->fields = [
            'id' => [
                'label' => __t('ID', 'Web'),
                'type' => 'input',
                'disabled' => true,
                'class' => 'md:col-span-6',
            ]
        ];
    }

    protected function initializeFields(): void
    {
        foreach ($this->fields as $field => $options) {
            if (!isset($this->data[$field])) {
                $this->data[$field] = null;
            }
        }
    }

    protected function resolveRepository()
    {
        if (class_exists($this->repositoryClass)) {
            return new $this->repositoryClass();
        }
    }

    public function prepareViewOptions(): array
    {
        return [];
    }

    public function delete()
    {
        if ($this->permissionForDeleting !== '') {
            $this->resolveRepository()->authorize($this->permissionForDeleting);
        }

        $ids = [$this->id];
        $this->resolveRepository()->delete($ids);
        $this->redirect(route($this->recordsRoute), navigate: true);
    }

    public function render()
    {
        return view($this->view, $this->prepareViewOptions());
    }
}
