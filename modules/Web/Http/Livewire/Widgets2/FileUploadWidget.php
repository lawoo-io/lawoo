<?php

namespace Modules\Web\Http\Livewire\Widgets2;


use Flux\Flux;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\File;
use Livewire\Component;
use Livewire\WithFileUploads;

class FileUploadWidget extends Component
{
    use WithFileUploads;

    public ?Model $model;
    public string $field = 'documents';
    public bool $multiple = false;

    public $file;

    public array $files = [];

    public $existingFiles = [];

    public string $componentId = '';

    public string $type;

    public string $label;

    public string $permissionForShow;
    public string $permissionForEdit;

    public bool $showPreview;

    public int $uploaderKey = 0;

    public string $description = '';

    public bool $setToPublic;

    public string $validationDescription = '';

    public function mount(string $field, array $options, string $permissionForShow, string $permissionForEdit): void
    {
        $this->model = $options['model'];
        $this->field = $field;
        $this->multiple = $options['multiple'] ?? false;

        $this->permissionForShow = $permissionForShow;
        $this->permissionForEdit = $permissionForEdit;

        $this->showPreview = $options['show_preview'] ?? false;

        if($this->model)
            $this->validationDescription = $options['description'] ?? $this->model->getFileValidationDescription($this->field);

        $this->setToPublic = $options['set_to_public'] ?? false;

        $this->componentId = $this->field . '_' . ($this->model->id ?? 'new') . '_' . uniqid();

        $this->loadExisting();

        $this->label = $options['label'];
    }

    public function loadExisting(): void
    {
        if($this->model)
            $this->existingFiles = $this->model->filesForField($this->field)->get();
    }

    public function updatedFile(): void
    {
        $this->saveFile();
    }

    public function updatedFiles(): void
    {
        $this->saveFile();
    }

    public function saveFile(): void
    {
        try {
            $rules = $this->model->getFileValidationRules($this->field);
            $this->validate([
                $this->multiple ? 'files.*' : 'file' => $rules,
            ]);

            if ($this->multiple) {
                File::createMultipleFromUpload(
                    $this->files,
                    $this->model,
                    $this->field,
                    $this->field
                );
                $this->reset('files');
            } else {
                if($this->model->{$this->field}()) {
                    $this->model->replaceFile($this->file, $this->field, $this->field);
                } else {
                    File::createFromUpload(
                        $this->file,
                        $this->model,
                        $this->field,
                        $this->field
                    );
                }
                $this->reset('file');
            }
            $this->resetUploader();
        } catch (\Throwable $e) {
            $messages = collect($this->getErrorBag()->all())->implode("\n");
            Flux::toast(text: $messages ?: $e->getMessage(), variant: 'danger');
            $this->resetUploader();
        }

        $this->loadExisting();
    }


    protected function resetUploader(): void
    {
        $this->reset('file', 'files');
        $this->resetErrorBag();
        $this->resetValidation();
        $this->uploaderKey++;
    }

    public function removeFile(int $id): void
    {
        $file = $this->model->files()->find($id);
        if ($file) {
            $file->deleteFile();
        }
        $this->loadExisting();
    }

    public function changeState(File $file): void
    {
        if($file->is_public) self::unpublicFile($file);
        else self::publicFile($file);
        $this->loadExisting();
    }

    protected function publicFile(File $file): void
    {
        $file->publish();
    }

    protected function unpublicFile(File $file): void
    {
        $file->unpublish();
    }

    public function render()
    {
        return view('livewire.web.widgets.file.upload-widget');
    }
}
