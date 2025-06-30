<?php

namespace Modules\Web\Http\Livewire\Messages;


use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;
use Modules\Core\Models\UserExtended;

class FormMessages extends Component
{
    /**
     * @var Model
     */
    public $model = null;

    /**
     * @var string
     */
    public string $permissionForAudit = '';

    /**
     * @var Model|null
     */
    #[Reactive]
    public ?Model $messagesModel = null;

    /**
     * @var array
     */
    public $messages = null;

    /**
     * @var int
     */
    public int $perPage = 20;

    /**
     * @var int
     */
    public int $page = 0;

    /**
     * @var bool
     */
    public bool $hasMore  = false;

    /**
     * @var int
     */
    public int $loadedCount = 0;

    public function mount(): void
    {
        $this->loadData();
    }

    #[On('load-messages')]
    public function loadData(): void
    {
        $this->messages = $this->messagesModel
            ->activeMessages()
            ->with(['user'])
            ->latest()
            ->take($this->perPage)
            ->get();

        $this->loadedCount = $this->messages->count();
        $this->checkIfHasMore();
    }

    public function loadMore(): void
    {
        $this->js('console.log("Test load more");');
        $newMessages = $this->messagesModel
            ->activeMessages()
            ->with(['user'])
            ->latest()
            ->skip($this->loadedCount)
            ->take($this->perPage)
            ->get();

        if ($newMessages->isNotEmpty()) {
            $this->messages = $this->messages->merge($newMessages);
            $this->loadedCount += $newMessages->count();
        }

        $this->checkIfHasMore();
    }

    private function checkIfHasMore(): void
    {
        $totalCount = $this->messagesModel->activeMessages()->count();
        $this->hasMore = $this->loadedCount < $totalCount;
    }

    public function render()
    {
        return view('livewire.web.messages.form-messages');
    }
}
