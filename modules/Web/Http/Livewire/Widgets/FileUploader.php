<?php

namespace Modules\Web\Http\Livewire\Widgets;


use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class FileUploader extends Component
{
    use WithFileUploads;

    public $model;
    public string $field = '';
    public $accept = '*';
    public bool $multiple = false;
    public bool $showPreview = true;
    public array $files = [];
    public object $existingFiles;
    public $file = null;
    public $existingFile;
    public $preview = false;

    public string $mode = '';

    public string $permissionForShow = '';
    public string $permissionForEdit = '';

    public string $componentId = '';

    public string $label = '';

    public string $imageClass = '';

    public array $fileRules = [];

    public bool $glightbox = false;

    public function mount(string $field, array $options = [], $permissionForShow = '', $permissionForEdit = ''): void
    {
        $this->existingFiles = collect();
        $this->model = $options['model'] ?? null;
        $this->field = $field;
        $this->accept = $options['accept'] ?? null;
        $this->mode = $options['mode'] ?? false;
        $this->multiple = $options['mode'] === 'images' || $options['mode'] === 'documents' ? true : false;
        $this->label = $options['label'] ?? null;
        $this->imageClass = $options['imageClass'] ?? 'w-17 h-17';
        $this->glightbox = $options['glightbox'] ?? false;

        $this->permissionForShow = $permissionForShow;
        $this->permissionForEdit = $permissionForEdit;

        $this->componentId = $this->field . '_' . ($this->model->id ?? 'new') . '_' . uniqid();

        if ($this->model) {
            $this->fileRules = $options['fileRules'] ?? $this->model->getFileValidationRules($this->mode);
            $this->loadExisting();
        }
    }

    public function hydrate()
    {
        // Files-Array nur für diese spezifische Komponente
        if (empty($this->files)) {
            $this->files = [];
        }
    }

    public function loadExisting(): void
    {
        if($this->mode == 'image' || $this->mode == 'document') {
            $this->existingFile = $this->model->{$this->field}()->first();
        } else {
            $this->existingFiles = $this->model->{$this->field}()->orderBy('id', 'desc')->get();
        }

    }

    public function getThumb($file, int $width = 200, int $height = 200, int $quality = 80): string
    {
        return $file->getThumbnailUrl($this->permissionForShow, $width, $height, $quality);
    }

    public function updatedFile(): void
    {
        $rules = $this->setRules();
        try {
            $this->validate($rules);
            if(isset($this->model)) {
                $this->saveFile();
            }
        } catch(\Illuminate\Validation\ValidationException $e) {
            Flux::toast(text: $e->getMessage(), variant: 'danger');
            $this->reset('file');
        }
    }

    public function updatedFiles(): void
    {
        $rules = $this->setRules();
        try {
            $this->validate($rules);
            if (isset($this->model)){
                $this->saveFile();
            }
        } catch(\Illuminate\Validation\ValidationException $e) {
            Flux::toast(text: $e->getMessage(), variant: 'danger');
            $this->reset('files');
        }
    }

    protected function setRules(): array
    {
        if ($this->mode === 'image' || $this->mode === 'document') {
            $rules = [
                'file' => $this->fileRules,
            ];
        } else {
            $rules = [
                'files' => 'array|min:1',
                'files.*' => $this->fileRules,
            ];
        }

        return $rules;
    }

    #[On('save-files')]
    public function saveFile(int $id = null, string $modelClass = null): void
    {
        if($modelClass && $id) {
            $this->model = $this->resolveRepository($modelClass)->find($id);
        }
        if ($this->mode === 'image' || $this->mode === 'images') {
            $type = 'images';
        } else if ($this->mode === 'document' || $this->mode === 'documents') {
            $type = 'documents';
        }

        if ($this->file && $this->mode === 'image' && $this->model) {
            $this->model->setImage($this->file, $this->field, $type);
            $this->reset('file');
        } else if($this->file && $this->mode === 'document') {
            $this->model->replaceFile($this->file, $this->field, $type);
            $this->reset('file');
        } else if ($this->files) {
            foreach ($this->files as $file) {
                $this->model->addFile($file, $this->field, $type);
            }
        }

        $this->loadExisting();
        $this->dispatch('reinit-glightbox-delayed');
    }

    protected function resolveRepository($modelClass)
    {
        if (class_exists($modelClass)) {
            return new $modelClass();
        }
    }

    public function getUrl(int $id): string
    {
        if ($this->mode === 'document' || $this->mode === 'image'){
            if($this->existingFile && $this->existingFile->id == $id) {
                return $this->existingFile->getUrl($this->permissionForShow);
            }
        } else {
            foreach($this->existingFiles as $file) {
                if($file->id === $id) {
                    return $file->getUrl($this->permissionForShow);
                }
            }
        }

        return '';
    }

    public function getDownloadUrl(int $id): string
    {
        if ($this->mode === 'document' || $this->mode === 'image'){
            if($this->existingFile && $this->existingFile->id == $id) {
                return $this->existingFile->getDownloadUrl($this->permissionForShow);
            }
        } else {
            foreach($this->existingFiles as $file) {
                if($file->id === $id) {
                    return $file->getDownloadUrl($this->permissionForShow);
                }
            }
        }

        return '';
    }

    public function removeFile(int $id): void
    {
        $user = auth()->user();
        if ($user->can($this->permissionForEdit)) {
            if($this->mode === 'image' || $this->mode === 'document') {
                if($this->existingFile->id === $id) {
                    $this->existingFile->deleteFile();
                    $this->reset('existingFile');
                }
            } elseif($this->mode === 'images' || $this->mode === 'documents') {
                $fileToDelete = $this->existingFiles->firstWhere('id', $id);
                if($fileToDelete) {
                    $fileToDelete->deleteFile();
                    // Collection neu laden
                    $this->loadExisting();
                }
            }
        }

        $this->dispatch('reinit-glightbox-delayed');

    }

    public function render()
    {
        return view('livewire.web.widgets.file.uploader');
    }
}
