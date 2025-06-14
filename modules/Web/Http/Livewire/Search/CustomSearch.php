<?php

namespace Modules\Web\Http\Livewire\Search;


use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Reactive;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Modules\Web\Models\UserSetting;

class CustomSearch extends Component
{
    #[Reactive]
    public ?array $panelFilters = [];

    #[Reactive]
    public ?array $searchFilters = [];

    public string $name;

    public bool $default = false;

    public bool $public = false;

    protected $key = 'search_users';

    protected $user_id = null;

    protected $data = null;

    public ?array $customItems = null;

    public function mount(): void
    {
        $this->user_id = auth()->id();
        $this->loadData();

        $default = collect($this->customItems)->firstWhere('default', true);
        if($default) {
            $this->setSearch($default['id']);
        }
    }

    protected function loadData(): void
    {
        $userSettings = UserSetting::model();
        $cacheTags = [$userSettings->getCacheTag()];
        $cacheKey = 'livewire:{$this->key}.{$this->user_id}';

        $this->customItems = Cache::tags($cacheTags)->remember($cacheKey, now()->addDay(), function () use ($userSettings) {
            return $userSettings->where('public', true)->active()->orWhere('user_id', $this->user_id)->active()->limit(20)->get()->toArray();
        });

    }

    protected function rules(): array
    {
        $rules = [
            'name'    => ['required', 'string', 'min:3', 'max:70'],
            'public'  => ['boolean'],
            'default' => ['boolean'],
        ];

        if ($this->default === true) {
            $rules['default'][] = Rule::unique('user_settings')
                ->where('key', $this->key);
        }

        return $rules;
    }


    protected function messages(): array
    {
        return [
            'default.unique' => __t('A default filter for this view already exists.', 'Web'),
        ];
    }

    public function save(): void
    {
        $this->user_id = auth()->id();
        $this->validate();

        $this->data = [
            'panelFilters' => $this->panelFilters,
            'searchFilters' => $this->searchFilters,
        ];

        $this->user_id = auth()->id();

        UserSetting::create(
            $this->only(['name', 'public', 'default', 'key', 'user_id', 'data']),
        );

        $this->reset('name', 'public', 'default');

        $this->loadData();
    }

    public function setSearch(string|int $id): void
    {
        $item = collect($this->customItems)->firstWhere('id', $id);

        if ($item && !empty($item)) {
            $this->dispatch('apply-saved-filter', filters: $item['data']);
        }
    }

    public function removeSearch(string|int $id, string $public): void
    {
        if((int)$public && !auth()->user()->can('web.search.delete_public')) return;

        $items = collect($this->customItems);
        $keyToRemove = $items->search(fn($item) => $item['id'] == $id);

        if ($keyToRemove !== false) {
            $items->splice($keyToRemove, 1);
            $this->customItems = $items->all();
        }

        UserSetting::find($id)?->delete();
    }

    public function render()
    {
        return view('livewire.web.search.custom-search');
    }
}
