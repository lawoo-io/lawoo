<?php

namespace Modules\Web\Http\Livewire\Form;

use Flux\Flux;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Core\Models\Language;
use Modules\Web\Models\Company;
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
    public string $permissionForShow = '';

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

    public bool $hasFiles = false;

    /**
     * @var string
     */
    public string $type = '';

    public bool $modal = false;

    public array $tabsWithErrors = [];

    public function mount(string $type = 'edit', int $id = null, bool $modal = false): void
    {
        $this->type = $type;
        $this->modal = $modal;
        if ($id) {
            $this->id = $id;
        } else {
            $this->id = request()->route('id');
        }

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
                if($field === 'tabs') {
                    foreach ($options as $tab => $tabFields) {
                        foreach ($tabFields['fields'] as $tabField => $tabOptions) {
                            $result[$tabField] = $this->record->$tabField;
                        }
                    }
                } else {
                    $result[$field] = $this->record->$field;
                }
            }
            $this->data = $result;
            $this->pageTitle = $this->data[array_key_first($this->fields)];
            $this->js("document.title = " . json_encode($this->pageTitle));
        } else {
            foreach ($this->fields as $field => $options) {
                if($field === 'tabs') {
                    foreach ($options as $tab => $tabFields) {
                        foreach ($tabFields['fields'] as $tabField => $tabOptions) {
                            if ($tabField === 'company_id') {
                                $this->data[$tabField] = collect(session('company_ids', []))->first() ?? null;
                            }
                        }
                    }
                } elseif ($field === 'company_id') {
                    $this->data[$field] = collect(session('company_ids', []))->first() ?? null;
                }
            }
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

        try {
            $this->tabsWithErrors = [];
            if ($this->rules) {
                $this->validate($this->rules);
            }

            if ($this->type === 'edit') {
                $result = $this->update();
                $this->dispatch('load-messages');
            } elseif ($this->type === 'create') {
                $this->createRecord();
            }

            $this->dispatch('render-code-editor');

            if ($result) {
                Flux::toast(text: __t('Updated successfully', 'Web'), variant: 'success');
            }

            if ($this->modal) {
                $this->dispatch('reload-form-data');
                $this->dispatch('close-modal-stack');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->tabsWithErrors = [];
            if (isset($this->fields['tabs'])) {
                foreach ($this->fields['tabs'] as $tabKey => $tabOptions) {
                    foreach ($tabOptions['fields'] as $field => $options) {
                        if ($e->validator->errors()->has('data.'.$field)) {
                            $this->tabsWithErrors[] = $tabKey;
                            break;
                        }
                    }
                }
            }

            foreach ($e->validator->errors()->all() as $error) {
                Flux::toast(text: $error, variant: 'danger');
            }
            throw $e; // wichtig, sonst bleibt Livewire in einem komischen Zustand
        }
    }

    #[On('reload-form-data')]
    public function reloadData(): void
    {
        $this->loadData();
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
        if ($id) {
            $this->dispatch('save-files', id: $id['id'], modelClass: $this->repositoryClass);
        }
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
            if (isset($options['type']) && $options['type'] === 'fileUploader') {
                $this->fields[$field]['model'] = $this->record;
            }
        }
    }

    protected function resolveRepository()
    {
        if (class_exists($this->repositoryClass)) {
            return new $this->repositoryClass();
        }
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

    #[On('file-uploader-form')]
    public function formCanSaved(): void
    {
        $this->hasFiles = true;
    }

    public function openModal(string $componentName, int $id = null, string $url = '', $type = 'edit'): void
    {
        $this->dispatch('open-modal-stack', componentName: $componentName, id: $id, url: $url, type: $type);
    }

    public function getCompanies(): ?array
    {
        return Company::whereIn('id', session()->get('company_ids', null))->pluck('name', 'id')->toArray();
    }

    public function generateSlugFromName(): void
    {
        if(isset($this->data['name']) && !empty($this->data['name'])) {
            $this->data['slug'] = Str::slug($this->data['name']);
        }
    }

    public function render()
    {
        return view($this->view);
    }
}
