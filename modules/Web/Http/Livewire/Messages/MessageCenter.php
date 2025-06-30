<?php

namespace Modules\Web\Http\Livewire\Messages;


use Flux\Flux;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Reactive;
use Livewire\Attributes\Validate;
use Livewire\Component;

class MessageCenter extends Component
{
    /**
     * @var bool
     */
    public bool $showEditor = false;

    public bool $showSubject = false;

    /**
     * @var string
     */
    #[Validate('required|string')]
    public string $message_type = '';

    /**
     * @var string
     */
    #[Validate('required|string')]
    public string $body = '';

    /**
     * @var string
     */
    public string $subject = '';

    /**
     * @var array
     */
    public array $bodyPlaceholders = [];

    /**
     * @var array
     */
    public array $subjectPlaceholders = [];

    /**
     * @var Model|null
     */
    #[Reactive]
    public ?Model $messagesModel = null;

    public function boot(): void
    {
        $this->bodyPlaceholders = [
            'email' => __t('Type a message...', 'Web'),
            'note' => __t('Type a note...', 'Web'),
            'default' => __t('Type a message...', 'Web'),
        ];

        $this->subjectPlaceholders = [
            'email' => __t('Type a subject...', 'Web'),
            'note' => __t('Type a title...', 'Web'),
            'default' => __t('Type a subject...', 'Web'),
        ];
    }

    public function save(): void
    {
        $this->validate();

        try {
            if ($this->message_type === 'email') {
                if (!$this->subject) $this->subject = $this->messagesModel->name ?? '';
                $this->addEmail();
                Flux::toast(text: __t('Successfully sent', 'Web'), variant: 'success');
            } elseif ($this->message_type === 'note') {
                $this->addNote();
                Flux::toast(text: __t('Successfully created', 'Web'), variant: 'success');
            }
        } catch (\Exception $e) {
            Flux::toast(text: $e->getMessage(), variant: 'danger');
        }

        $this->dispatch('load-messages');
    }

    protected function addEmail(): void
    {
        $this->messagesModel->addEmail($this->subject, $this->body);
    }

    protected function addNote(): void
    {
        $this->messagesModel->addNote($this->subject, $this->body);
    }

    public function activateEditor(): void
    {
        $this->showEditor = true;
        if (!$this->message_type)
            $this->message_type = 'email';
    }

    public function deactivateEditor(): void
    {
        $this->showEditor = false;
        $this->body = '';
    }

    public function setEmailType(): void
    {
        $this->message_type = 'email';
        $this->showEditor = true;
    }

    public function setNoteType(): void
    {
        $this->message_type = 'note';
        $this->showEditor = true;
    }

    public function toggleSubject(): void
    {
        $this->showSubject = !$this->showSubject;
    }

    public function render()
    {
        return view('livewire.web.messages.message-center');
    }
}
