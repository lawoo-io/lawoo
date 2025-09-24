<?php

namespace Modules\Newsletter\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseController;
use Modules\Newsletter\Models\NewsletterSubscriber;

class NewsletterController extends BaseController
{
    public function confirm(Request $request, string $token)
    {
        $subscriber = NewsletterSubscriber::where('confirmation_token', $token)->firstOrFail();

        $subscriber->update([
            'confirmed_at' => now(),
            'confirmation_token' => null,
        ]);

        return view('newsletter.confirmed'); // Danke-Seite
    }
}
