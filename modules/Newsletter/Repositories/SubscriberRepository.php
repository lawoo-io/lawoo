<?php

namespace Modules\Newsletter\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Modules\Core\Abstracts\BaseRepository;
use Modules\Newsletter\Mail\ConfirmSubscription;
use Modules\Newsletter\Models\NewsletterSubscriber;

class SubscriberRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new NewsletterSubscriber());
    }

    public function create(array $data): Model
    {
        $model = parent::create($data);
        self::syncCampaigns($model, $data['campaigns']);
        self::sendConfirmationEmail($model);
        return $model;
    }

    public function findByToken(string $token): ?Model
    {
        return $this->model->where('confirmation_token', $token)->where('status', 'pending')->first();
    }

    public function confirm(string $token): bool
    {
        $subscriber = $this->model->where('confirmation_token', $token)->firstOrFail();
        $subscriber->status = 'active';
        $subscriber->confirmation_token = null;
        $subscriber->confirmed_at = now();
        $subscriber->save();
        return true;
    }

    public static function sendConfirmationEmail(Model $model): void
    {
        $model->confirmation_token = Str::random(64);
        $model->status = 'pending';
        $model->save();

        $url = self::signConfirmationUrl($model, $model->confirmation_token);

        Mail::to($model->email)->send(new ConfirmSubscription($model, $url));
    }

    protected static function signConfirmationUrl(Model $model, string $token): string
    {
        $baseUrl = url("/newsletter/confirm/{$token}");
        $expires = now()->addDay()->getTimestamp();

        $signature = hash_hmac(
            'sha256',
            $baseUrl.'?expires='.$expires,
            config('app.key')
        );

        return $baseUrl.'?expires='.$expires.'&signature='.$signature;
    }

    protected function syncCampaigns(Model $model, array $campaigns): void
    {
        $result = [];

        foreach ($campaigns as $key => $campaign) {
            $result[$key] = ['status' => $model->status ?? 'pending', 'subscribed_at' => now()];
        }

        $model->campaigns()->sync($result);
    }
}
