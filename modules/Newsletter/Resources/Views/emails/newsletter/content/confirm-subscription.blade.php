{{--
name: 'email_confirm_subscription_content',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
@extends('emails.newsletter.layouts.default')

@section('content')
    <h2>Bestätige Ihrer Anmeldung</h2>
    <p>Hallo {{ (!empty($subscriber->first_name) || !empty($subscriber->last_name)) ? $subscriber->first_name . ' ' . $subscriber->last_name : $subscriber->email }},</p>
    <p>vielen Dank für die Anmeldung zu unserem Newsletter!
        Bitte bestätigen Sie Ihre E-Mail-Adresse, indem Sie auf den folgenden Link klicken:</p>

    <p>
        <a href="{{ $confirmationUrl }}"
           style="background:#0092B9;color:#ffffff;padding:10px 20px;
                  text-decoration:none;border-radius:6px;display:inline-block;">
            Anmeldung bestätigen
        </a>
    </p>

    <p>Wenn Sie sich nicht für unseren Newsletter angemeldet haben, können Sie diese E-Mail ignorieren.</p>

    <p>Viele Grüße<br>
        Ihr Team</p>
@endsection
