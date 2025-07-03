<?php

namespace Modules\Web\Http\Livewire\Form;

use Flux\Flux;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\Core\Models\Language;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
    protected string $repositoryClass = '';

    /**
     * @var string
     */
    public string $view = 'livewire.web.form.base-form-view';

    /**
     * From URL
     * @var
     */
    public $id;

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

    /**
     * @var string
     */
    public string $recordRoute = '';

    /**
     * @var bool
     */
    public bool $showMessages = false;

    /**
     * @var Model|null
     */
    public ?Model $messagesModel = null;

    /**
     * @var
     */
    public $record;

    /**
     * @var string
     */
    public string $pageTitle = '';

    public array $locales = [];

    public string $locale = '';

    public string $defaultLocale = '';

    public array $translatableFields = [];

    /**
     * @var string
     */
    public string $type = '';

    public function mount(string $type = 'edit'): void
    {
        $this->type = $type;
        $this->id = request()->route('id');

        if ($this->type === 'create') {
            $this->pageTitle = __t('Create', 'Web');
        }

        $this->setFields();
        $this->loadData();
        $this->setRules();
        $this->initializeFields();
        $this->getMessageModel();
        $this->getLocales();
        $this->getTranslatableFields();

    }

    protected function loadData(): void
    {
        if ($this->id) {
            $this->record = $this->getRecord();
            if (!$this->record) {
                throw new NotFoundHttpException();
            }
            $result = [];
            foreach ($this->fields as $field => $options) {
                $result[$field] = $this->record->$field;
            }
            $this->data = $result;
            $this->pageTitle = $this->data[array_key_first($this->fields)];
            $this->js("document.title = " . json_encode($this->pageTitle));
        }
    }

    public function getMessageModel(): void
    {
        if ($this->showMessages && $this->id) {
             $this->messagesModel = $this->resolveRepository()->find($this->id);
        }
    }

    protected function getRecord()
    {
        if($this->id) return $this->resolveRepository()->find($this->id);
        return null;
    }

    protected function getTranslatableFields(): void
    {
        $this->translatableFields = $this->resolveRepository()->getTranslatableFields();
    }

    protected function getLocales(): void
    {
        $this->locales = Language::active()->pluck('name', 'code')->toArray();
        $this->locale = config('app.locale');
        $this->defaultLocale = Language::getDefault()->first()->code;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
        foreach ($this->translatableFields as $field) {
            if ($this->locale !== config('app.locale')) {
                $this->data[$field] = $this->record->lang($this->locale)->$field;
            } else {
                $this->data[$field] = $this->record->$field;
            }
        }
    }

    public function setRules(): void
    {
        $this->rules = [];
    }

    public function save(): void
    {
        $result = false;
        if($this->rules) $this->validate($this->rules);

        if($this->type === 'edit') {
            $result = $this->update();
            $this->dispatch('load-messages');
        } elseif($this->type === 'create') {
            $this->createRecord();
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

        return $this->resolveRepository()->update($this->id, $this->data, $this->locale);
    }

    public function createRecord(): void
    {
        $id = $this->resolveRepository()->create($this->data);
        if ($this->recordRoute){
            $this->redirectRoute($this->recordRoute, ['id' => $id], navigate: true);
        }
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
                $this->data[$field] = $options['default'] ?? null;
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

    public function deleteRecord(): void
    {
        if ($this->permissionForDeleting !== '') {
            $this->resolveRepository()->authorize($this->permissionForDeleting);
        }

        $ids = [$this->id];
        $this->resolveRepository()->delete($ids);
    }

    public function delete()
    {
        $this->deleteRecord();
        $this->redirect(route($this->recordsRoute), navigate: true);
    }

    public function render()
    {
        return view($this->view, $this->prepareViewOptions());
    }
}
