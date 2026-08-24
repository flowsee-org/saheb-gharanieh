@php
    $icons = ['general' => 'sparkle', 'contact' => 'account', 'social' => 'eye'];
@endphp

<x-layouts.admin title="متن‌های سایت" heading="متن‌های سایت"
                 subheading="معرفی کافه، ساعات کاری و راه‌های تماس — همان چیزی که در صفحهٔ اصلی و پانویس دیده می‌شود">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="admin-form">
        @csrf
        @method('PUT')

        <div class="admin-form-grid">
            @foreach ($groups as $group => $groupLabel)
                <div class="admin-form-col">
                    <x-admin.card :title="$groupLabel" :icon="$icons[$group] ?? 'settings'">
                        @foreach ($settings[$group] as $setting)
                            <x-admin.field :label="$setting->label ?: $setting->key"
                                           :name="'values.'.$setting->key"
                                           :for="'setting-'.$setting->key">
                                @if ($setting->type === 'text')
                                    <textarea class="admin-input admin-textarea admin-textarea--tall"
                                              id="setting-{{ $setting->key }}"
                                              name="values[{{ $setting->key }}]"
                                              rows="6"
                                              maxlength="2000">{{ old('values.'.$setting->key, $setting->value) }}</textarea>
                                @else
                                    <input type="text"
                                           class="admin-input"
                                           id="setting-{{ $setting->key }}"
                                           name="values[{{ $setting->key }}]"
                                           value="{{ old('values.'.$setting->key, $setting->value) }}"
                                           @if ($group === 'social') dir="ltr" @endif
                                           maxlength="400">
                                @endif
                            </x-admin.field>
                        @endforeach
                    </x-admin.card>
                </div>
            @endforeach
        </div>

        <div class="admin-form-bar">
            <button type="submit" class="admin-btn admin-btn--accent">
                <x-icon.admin name="check" class="h-4 w-4" />
                <span>ذخیرهٔ متن‌ها</span>
            </button>

            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="admin-btn admin-btn--quiet">
                <x-icon.admin name="eye" class="h-4 w-4" />
                <span>دیدن نتیجه در سایت</span>
            </a>
        </div>
    </form>
</x-layouts.admin>
