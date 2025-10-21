<?php

namespace Modules\Web\Http\Livewire\Widgets;


use Modules\Core\Models\File;
use Modules\Web\Http\Livewire\Widgets\FileUploadWidget;

class AvatarWidget extends FileUploadWidget
{
    public ?File $avatar;

    public function loadExisting(): void
    {
        $this->avatar = $this->model->{$this->field}()->first();
    }

    public function saveFile(): void
    {
        parent::saveFile();
    }

    public function removeAvatar(): void
    {

    }

    public function render()
    {
        return view('livewire.web.widgets.avater.index');
    }
}
