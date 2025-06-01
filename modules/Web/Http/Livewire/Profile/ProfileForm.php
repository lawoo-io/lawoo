<?php

namespace Modules\Web\Http\Livewire\Profile;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Modules\Core\Models\Language;

class ProfileForm extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    public string $email = '';

    public $selectedLanguage;

    protected function rules()
    {
        return [
            'email' => 'required|email|unique:users,email,' . auth()->id(),
            'selectedLanguage' => 'nullable|exists:languages,id'
        ];
    }

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selectedLanguage = $user->language_id;
    }

    public function updatedSelectedLanguage($value)
    {
        // Sofort speichern wenn Sprache geändert wird
        $user = auth()->user();
        $user->language_id = $value;
        $user->save();

        // Session setzen für sofortige Wirkung
        $language = Language::find($value);
        if ($language) {
            session(['locale' => $language->code]);
            app()->setLocale($language->code);
            $this->js('window.location.reload()');
        }
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

    public function getActiveLanguages()
    {
        return Language::query()->active()->ordered()->get();
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
