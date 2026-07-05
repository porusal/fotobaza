@props([
    'name',
    'label',
    'value' => '',
    'placeholder' => '',
])

@php
    $fieldId = preg_replace('/[^A-Za-z0-9_-]+/', '_', $name);
    $inputId = $fieldId . '_input';
    $editorId = $fieldId . '_editor';
    $fieldValue = old($name, $value ?? '');
@endphp

<div class="rich-editor-field" data-quill-field>
    <label class="form-label" for="{{ $inputId }}">{{ $label }}</label>

    <textarea
        id="{{ $inputId }}"
        name="{{ $name }}"
        class="rich-editor__input d-none"
        data-quill-input
    >{{ $fieldValue }}</textarea>

    <div
        id="{{ $editorId }}"
        class="rich-editor"
        data-quill-editor
        data-placeholder="{{ $placeholder }}"
    ></div>

    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
