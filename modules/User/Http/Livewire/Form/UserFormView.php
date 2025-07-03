<?php

namespace Modules\User\Http\Livewire\Form;

use Flux\Flux;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Modules\User\Repositories\UserRepository;
use Modules\Web\Http\Livewire\Form\BaseFormView;

class UserFormView extends BaseFormView
{
    protected string $repositoryClass = UserRepository::class;

    public string $permissionForEdit = 'user.users.edit';

    public string $permissionForDeleting = 'user.users.delete';

    public string $recordsRoute = 'lawoo.users.records';

    public bool $showRightContent = true;

    public bool $showMessages = true;

    public bool $isVerified = false;

    public function setFields(): void
    {

        $this->fields = [
            'name' => [
                'type' => 'input',
                'label' => __t('Name', 'User'),
                'class' => 'lg:col-span-6',
            ],
            'email' => [
                'type' => 'input',
                'label' => __t('Email', 'User'),
                'class' => 'lg:col-span-6',
            ],
            'language_id' => [
                'type' => 'select',
                'label' => __t('Language', 'User'),
                'class' => 'lg:col-span-6',
                'options' => $this->getLanguageOptions(),
            ],
            'is_super_admin' => [
                'type' => 'switch',
//                'mode' => 'inline',
                'label' => __t('Super-Admin', 'User'),
                'class' => 'lg:col-span-6',
                'disabled' => $this->id === auth()->id(),
            ],
            'tabs' => [
                'tab_first' => [
                    'label' => __t('Roles', 'User'),
                    'class' => 'w-full',
                    'fields' => [
                        'roles' => [
                            'type' => 'checkbox',
                            'mode' => 'cards',
                            'label' => __t('Roles', 'User'),
                            'class' => 'md:col-span-12',
                            'options' => $this->getRoleOptions(),
                        ],
                    ]
                ],
                'tab_second' => [
                    'label' => __t('Companies', 'User'),
                    'class' => 'w-full',
                    'fields' => [
                        'companies' => [
                            'type' => 'checkbox',
                            'mode' => 'cards',
                            'label' => __t('Companies', 'User'),
                            'class' => 'md:col-span-12',
                            'options' => $this->getCompanyOptions(),
                        ]
                    ]
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
        parent::loadData();
        if ($this->record){
            if ($this->record->email_verified_at){
                $this->isVerified = true;
            }
            $this->data['roles'] = $this->record->roles->pluck('id')->toArray();
            $this->data['companies'] = $this->record->companies->pluck('id')->toArray();
        }
    }

    protected function update(): ?Model
    {
        $model = parent::update();
        if($model) {
            $model->trackBelongsToManySync('roles', $this->data['roles']);
            $model->roles()->sync($this->data['roles']);

            $model->trackBelongsToManySync('companies', $this->data['companies']);
            $model->companies()->sync($this->data['companies']);

            $this->dispatch('companies-refresh');
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

    protected function getCompanyOptions(): array
    {
        return $this->resolveRepository()->getCompanyOptions();
    }

    public function prepareViewOptions(): array
    {
        $options = parent::prepareViewOptions();

        if ($this->id) {
            $userFormView = view('livewire.user.form.user-form-view', ['data' => $this->data]);
            $options['formTopLeft'] = $userFormView->renderSections()['formTopLeft'] ?? null;
            $options['formTopRight'] = $userFormView->renderSections()['formTopRight'] ?? null;
            $options['headerCenter'] = $userFormView->renderSections()['headerCenter'] ?? null;
        }

        return $options;
    }

    public function delete()
    {
        if ($this->id === auth()->id()) {
            Flux::toast(text: __t("You can't delete yourself!", "User"), variant: 'danger');
            return false;
        }
        parent::delete();
    }

    public function render()
    {
        View::share('livewireComponent', $this);
        return view($this->view, $this->prepareViewOptions());
    }

}
