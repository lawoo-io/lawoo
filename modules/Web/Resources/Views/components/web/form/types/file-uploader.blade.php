{{--
name: 'form_type_file_uploader',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
@props([
    'field',
    'options'
])



<div class="{{ $options['class'] ?? '' }}">
    @livewire('web.widgets.file-uploader', [
    'field' => $field,
    'options' => $options,
    'permissionForShow' => $this->permissionForShow,
    'permissionForEdit' => $this->permissionForEdit,
], key('file-uploader-' . $field . '-' . ($options['model']->id ?? 'new')))
</div>
