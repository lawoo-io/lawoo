<?php

namespace Modules\Web\Http\Livewire\Profile;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ProfileForm extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    public string $email = '';

    protected function rules()
    {
        return [
            'email' => 'required|email|unique:users,email,' . auth()->id()
        ];
    }

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function submit(): void
    {
        $user = auth()->user();

        $validated = $this->validate();

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    public function render()
    {
        return view('livewire.web.profile.profile-form');
    }
}
