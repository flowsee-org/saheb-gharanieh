{{-- On/off control. A real checkbox underneath, so it submits, tabs and reads
     correctly; the accent track is drawn from its :checked state in CSS. --}}
@props(['name', 'label', 'checked' => false, 'hint' => null, 'value' => 1])

@php $id = $name.'-switch'; @endphp

<div {{ $attributes->class('admin-switch') }}>
    {{-- The unchecked value: browsers omit an unticked checkbox entirely. --}}
    <input type="hidden" name="{{ $name }}" value="0">

    <input type="checkbox"
           class="admin-switch-input"
           id="{{ $id }}"
           name="{{ $name }}"
           value="{{ $value }}"
           @checked(old($name, $checked))>

    <label class="admin-switch-label" for="{{ $id }}">
        <span class="admin-switch-track" aria-hidden="true"><span class="admin-switch-knob"></span></span>

        <span class="admin-switch-text">
            <span class="admin-switch-name">{{ $label }}</span>
            @if ($hint)
                <span class="admin-switch-hint">{{ $hint }}</span>
            @endif
        </span>
    </label>
</div>
