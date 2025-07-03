<?php

namespace Modules\Web\Http\Livewire\widgets;


use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Component;

class CompanyWidget extends Component
{
    public array $companies = [];

    public array $selected = [];

    public string $cacheKey = '';

    public function mount(): void
    {
        $this->cacheKey = 'user_companies_' . auth()->id();
        $this->loadData();
    }

    protected function loadData(): void
    {
        $this->companies = Cache::remember($this->cacheKey, now()->addDays(intval(settings('cache_settings_records'))), function () {
            return auth()->user()->companies()->active()->pluck('name', 'id')->toArray();
        });

        if(count($this->companies) > 0){
            $this->setSelected();
        }
    }

    #[On('companies-refresh')]
    public function reload(): void
    {
        Cache::forget($this->cacheKey);
        $this->loadData();
    }

    public function update(int $key): void
    {
        if(in_array($key, $this->selected) && count($this->selected) > 1) {
            $this->selected = array_filter($this->selected, function($value) use ($key) {
                return $value !== $key;
            });
        } elseif(!in_array($key, $this->selected)) {
            $this->selected[] = $key;
        }

        session()->put('company_ids', $this->selected);
        $this->redirect(request()->header('Referer'), navigate: true);
    }

    protected function setSelected(): void
    {
        $this->selected = session()->get('company_ids', []);
        if (!count($this->selected) && count($this->companies)) {
            $this->selected = [array_key_first($this->companies),];
            session()->put('company_ids', $this->selected);
            $this->selected = session()->get('company_ids', []);
        }
    }

    public function getSelectedName(): string
    {
        if (count($this->selected) === 1) {
            if (!isset($this->companies[$this->selected[array_key_first($this->selected)]])){
                session()->put('company_ids', []);
                $this->setSelected();
            }
            return $this->companies[$this->selected[array_key_first($this->selected)]];
        } else {
            return count($this->selected) . ' ' . __t('selected', 'Web');
        }
    }

    public function checkChecked(int $key): bool
    {
        $checked = false;
        foreach ($this->selected as $item) {
            if ($item === $key) {
                $checked = true;
            }
        }
        return $checked;
    }

    public function render()
    {
        return view('livewire.web.widgets.company.index');
    }
}
