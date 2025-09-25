<?php

namespace Modules\Newsletter\Http\Livewire\Website;

use Livewire\Component;
use Modules\Newsletter\Repositories\SubscriberRepository;

class SubscribeForm extends Component
{
    public array $campaigns;
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public bool $privacy = false;
    public bool $success = false;
    public bool $showContactBtn;

    public function mount(array $campaigns, bool $showContactBtn = false): void
    {
        $this->campaigns = $campaigns;
        $this->showContactBtn = $showContactBtn;
    }

    protected function rules(): array
    {
        return [
            'first_name' => 'string|min:2',
            'last_name' => 'string|min:2',
            'email' => 'required|string|email|max:150|unique:newsletter_subscribers',
            'privacy' => 'accepted',
        ];
    }

    public function submit(): void
    {
        $validated = $this->validate($this->rules());

        $data = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'campaigns' => $this->campaigns,
        ];

        $subscriber = new SubscriberRepository();
        $subscriber->create($data);

        session()->flash('status', 'Post successfully updated.');

        $this->reset();

        $this->success = true;
    }

    public function render()
    {
        return view('livewire.newsletter.website.subscribe-form');
    }
}
