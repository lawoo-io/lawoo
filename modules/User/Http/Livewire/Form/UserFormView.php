<?php

namespace Modules\User\Http\Livewire\Form;

use Flux\Flux;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Modules\Web\Http\Livewire\Form\BaseFormView;
use function Laravel\Prompts\clear;

class UserFormView extends BaseFormView
{
    protected $repositoryClass = "Modules\\User\\Repositories\\UserRepository";

    public function setFields(): void
    {

        $this->fields = [
            'name' => [
                'type' => 'input',
                'label' => __t('Name', 'User'),
                'class' => 'md:col-span-6',
            ],
            'email' => [
                'type' => 'input',
                'label' => __t('Email', 'User'),
                'class' => 'md:col-span-6',
            ],
            'language_id' => [
                'type' => 'select',
                'label' => __t('Language', 'User'),
                'class' => 'md:col-span-6',
                'options' => $this->getLanguageOptions(),
            ],
            'is_super_admin' => [
                'type' => 'switch',
//                'mode' => 'inline',
                'label' => __t('Super-Admin', 'User'),
                'class' => 'md:col-span-6',
                'disabled' => $this->id === auth()->id(),
            ],
            'tabs' => [
                'tab_first' => [
                    'label' => __t('Roles', 'User'),
                    'class' => 'md:col-span-6',
                    'fields' => [
                        'roles' => [
                            'type' => 'checkbox_group',
                            'mode' => 'cards',
                            'label' => __t('Roles', 'User'),
                            'class' => 'md:col-span-12',
                            'options' => $this->getRoleOptions(),
                        ],
                    ]
                ],
                'tab_second' => [
                    'label' => 'Tab 2',
                    'fields' => []
                ]
            ]
        ];
    }

    public function setRules(): void
    {
        $this->rules = [
            'data.name' => 'required|string|min:3|max:256',
            'data.email' => 'required|string|email|max:256',
        ];
    }

    protected function loadData(): void
    {
        $record = $this->resolveRepository()->find($this->id);
        $this->data = $record->toArray();
        $this->data['roles'] = $record->roles->pluck('id')->toArray();
    }

    protected function update(): ?Model
    {
        $model = parent::update();
        if($model) {
            $model->roles()->sync($this->data['roles']);
        }

        return $model;
    }

    public function sendVerificationEmail()
    {
        Flux::toast(text: 'sendVerificationEmail function', variant: 'success');
    }

    public function resetPassword()
    {
        Flux::toast(text: 'resetPassword function', variant: 'success');
    }

    public function getRoleOptions(): array
    {
        return $this->resolveRepository()->getRoleOptions();
    }

    public function getLanguageOptions(): array
    {
        return $this->resolveRepository()->getLanguageOptions();
    }

    public function prepareViewOptions(): array
    {
        $options = parent::prepareViewOptions();

        $userFormView = view('livewire.user.form.user-form-view', ['data' => $this->data]);
        $options['formTopLeft'] = $userFormView->renderSections()['formTopLeft'] ?? null;
        $options['formTopRight'] = $userFormView->renderSections()['formTopRight'] ?? null;
        $options['headerCenter'] = $userFormView->renderSections()['headerCenter'] ?? null;

        return $options;
    }

    public function delete()
    {
        if ($this->id === auth()->id()) {
            Flux::toast(text: __t("You can't delete yourself!", "User"), variant: 'danger');
            return;
        }
        parent::delete();

        return $this->redirect(route('lawoo.users.lists'), navigate: true);
    }

    public function render()
    {
        View::share('livewireComponent', $this);
        return view($this->view, $this->prepareViewOptions());
    }

}
