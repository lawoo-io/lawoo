<?php

namespace Modules\Newsletter\Http\Livewire\Website;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Livewire\Component;
use Modules\Newsletter\Repositories\SubscriberRepository;

class ConfirmSubscriber extends Component
{
    public string $status;
    public string $token;

    public function mount(Request $request, string $token): void
    {
        $this->token = $token;
        if (!URL::hasValidSignature($request)) {
            $subscriber = (new SubscriberRepository())->findByToken($token);

            if (!$subscriber) {
                abort(401, __t('Invalid or expired link', 'Newsletter'));
            }

            $this->status = 'expired';

        } else {
            $result = (new SubscriberRepository())->confirm($token);
            if (!$result) {
                abort(401, __t('Invalid or expired link', 'Newsletter'));
            }
            $this->status = 'success';
        }
    }

    public function sendConfirm(): void
    {
        $subscriber = (new SubscriberRepository())->findByToken($this->token);
        if (!$subscriber) {
            abort(401, __t('Invalid or expired link', 'Newsletter'));
        }
        (new SubscriberRepository())->sendConfirmationEmail($subscriber);
        $this->status = 'resent';
    }

    public function render()
    {
        return view('livewire.newsletter.website.confirm-subscriber');
    }
}
