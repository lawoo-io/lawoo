<?php

namespace Modules\Website\Http\Livewire\Widgets;


use Livewire\Component;
use Modules\Website\Repositories\WebsiteRepository;

class WebsiteSelectorWidget extends Component
{
    public string $cacheKey;
    public $websites;
    public int|null $website_id = null;

    public function mount(): void
    {
        $this->cacheKey = 'user_websites_' . auth()->id();
        $this->loadData();
    }

    protected function loadData(): void
    {
        $websiteRepository = new WebsiteRepository();

        $this->websites = $websiteRepository->getFilteredData()->pluck('name', 'id')->toArray();

        if(is_countable($this->websites) && count($this->websites)) {
            $this->getSelected();
        }
    }

    protected function getSelected(): void
    {
        $this->website_id = session()->get('website_id', null);
        if(!$this->website_id) {
            $this->setSelected();
        }

        if(!isset($this->websites[$this->website_id])) {
            $this->setSelected();
        }
    }

    protected function setSelected(): void
    {
        $this->website_id = array_key_first($this->websites);
        session()->put('website_id', $this->website_id);
    }

    public function update(int $key): void
    {
        session()->put('website_id', $key);
        $this->redirect(request()->header('referer'), navigate: true);
    }

    public function render()
    {
        return view('livewire.website.widgets.website-selector');
    }
}
