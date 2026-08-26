@php
    $editing = $category->exists;
    $onLanding = old('show_on_landing', $category->card_order !== null);
@endphp

<x-layouts.admin :title="$editing ? 'ویرایش دسته' : 'دستهٔ جدید'"
                 :heading="$editing ? $category->name : 'دستهٔ جدید'"
                 :subheading="$editing ? 'ویرایش این بخش از منو' : 'یک بخش تازه به منو اضافه کنید'">
    <x-slot:actions>
        <a href="{{ route('admin.categories.index') }}" class="admin-btn admin-btn--quiet">
            <x-icon.admin name="right" class="h-4 w-4" />
            <span>بازگشت به دسته‌ها</span>
        </a>
    </x-slot:actions>

    <form method="POST"
          action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
          class="admin-form">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="admin-form-grid">
            <div class="admin-form-col">
                <x-admin.card title="مشخصات بخش" icon="categories">
                    <x-admin.field label="نام دسته" name="name" required hint="عنوانی که بالای بخش چاپ می‌شود.">
                        <input type="text" class="admin-input" id="name" name="name"
                               value="{{ old('name', $category->name) }}" required autofocus maxlength="80">
                    </x-admin.field>

                    <x-admin.field label="نام کوتاه" name="short_name"
                                   hint="اختیاری — برای نوار بالای منو، جایی که فضا کم است.">
                        <input type="text" class="admin-input" id="short_name" name="short_name"
                               value="{{ old('short_name', $category->short_name) }}" maxlength="40">
                    </x-admin.field>

                    <x-admin.field label="نام لاتین" name="latin_name" hint="اختیاری — مثلاً HOT DRINKS.">
                        <input type="text" class="admin-input latin-input" id="latin_name" name="latin_name"
                               value="{{ old('latin_name', $category->latin_name) }}" dir="ltr" maxlength="80">
                    </x-admin.field>

                    <x-admin.field label="زیرعنوان" name="subtitle"
                                   hint="اختیاری — یک خط زیر عنوان بخش.">
                        <input type="text" class="admin-input" id="subtitle" name="subtitle"
                               value="{{ old('subtitle', $category->subtitle) }}" maxlength="160">
                    </x-admin.field>

                    <x-admin.field label="توضیح" name="description">
                        <textarea class="admin-input admin-textarea" id="description" name="description"
                                  rows="3" maxlength="600">{{ old('description', $category->description) }}</textarea>
                    </x-admin.field>

                    <x-admin.field label="نشانی بخش" name="slug"
                                   hint="لاتین و با خط تیره؛ در آدرس /menu/… استفاده می‌شود. خالی بگذارید تا خودکار ساخته شود.">
                        <input type="text" class="admin-input latin-input" id="slug" name="slug"
                               value="{{ old('slug', $category->slug) }}" dir="ltr" maxlength="80"
                               placeholder="hot-drinks">
                    </x-admin.field>
                </x-admin.card>

                <x-admin.card title="نقش تزئینی" icon="sparkle"
                              subtitle="نقشی که کنار عنوان بخش و روی کارت صفحهٔ اصلی دیده می‌شود">
                    <x-admin.glyph-picker name="glyph" :groups="$glyphGroups"
                                          :selected="$category->glyph" label="نقش این بخش" />
                </x-admin.card>
            </div>

            <div class="admin-form-col">
                <x-admin.card title="نوع و چیدمان" icon="items">
                    <x-admin.field label="نوع بخش" name="kind" required
                                   hint="بخش قلیان نقش و چیدمان مخصوص خودش را می‌گیرد.">
                        <select class="admin-select" id="kind" name="kind" required>
                            @foreach ($kinds as $kind)
                                <option value="{{ $kind->value }}"
                                        @selected(old('kind', $category->kind?->value) === $kind->value)>
                                    {{ $kind->label() }}
                                </option>
                            @endforeach
                        </select>
                    </x-admin.field>

                    <x-admin.field label="چیدمان" name="layout" required
                                   hint="«شبکه‌ای» برای موارد با تصویر، «لیستی» برای فهرست‌های بلند طعم.">
                        <select class="admin-select" id="layout" name="layout" required>
                            @foreach ($layouts as $layout)
                                <option value="{{ $layout->value }}"
                                        @selected(old('layout', $category->layout?->value) === $layout->value)>
                                    {{ $layout->label() }}
                                </option>
                            @endforeach
                        </select>
                    </x-admin.field>

                    {{-- «قیمت سرویس» and «توضیح قیمت» used to sit here. They were
                         the one-price-for-the-whole-section pair the hookah menu
                         printed; the hookah flavours are priced one by one in
                         «موارد منو» now, so these two boxes had nothing left to
                         show on the site and were a control that did nothing. The
                         columns are still there, holding whatever was typed. --}}
                </x-admin.card>

                <x-admin.card title="نمایش" icon="eye">
                    <x-admin.switch name="is_active" label="نمایش این بخش در منو"
                                    hint="خاموش کنید تا کل بخش و موارد داخلش از منو پنهان شود."
                                    :checked="(bool) $category->is_active" />

                    <x-admin.switch name="show_on_landing" label="کارت در صفحهٔ اصلی"
                                    hint="این بخش به‌عنوان یکی از کارت‌های بزرگ صفحهٔ اول نشان داده می‌شود."
                                    :checked="(bool) $onLanding" />

                    <x-admin.field label="ترتیب در منو" name="sort_order"
                                   hint="عدد کوچک‌تر بالاتر می‌آید. با کشیدن ردیف‌ها در فهرست هم قابل تغییر است.">
                        <input type="text" class="admin-input admin-input--narrow" id="sort_order" name="sort_order"
                               value="{{ old('sort_order', \App\Support\Persian::digits($category->sort_order ?? 0)) }}"
                               inputmode="numeric">
                    </x-admin.field>
                </x-admin.card>

                <x-admin.card title="کارت صفحهٔ اصلی" icon="sparkle"
                              subtitle="فقط وقتی کارت صفحهٔ اصلی روشن است دیده می‌شود">
                    <x-admin.field label="عنوان کارت" name="card_title"
                                   hint="خالی بگذارید تا همان نام دسته استفاده شود.">
                        <input type="text" class="admin-input" id="card_title" name="card_title"
                               value="{{ old('card_title', $category->card_title) }}" maxlength="80">
                    </x-admin.field>

                    <x-admin.field label="زیرعنوان کارت" name="card_subtitle">
                        <input type="text" class="admin-input" id="card_subtitle" name="card_subtitle"
                               value="{{ old('card_subtitle', $category->card_subtitle) }}" maxlength="160">
                    </x-admin.field>

                    <x-admin.field label="نام لاتین کارت" name="card_latin">
                        <input type="text" class="admin-input latin-input" id="card_latin" name="card_latin"
                               value="{{ old('card_latin', $category->card_latin) }}" dir="ltr" maxlength="80">
                    </x-admin.field>
                </x-admin.card>
            </div>
        </div>

        <div class="admin-form-bar">
            <button type="submit" class="admin-btn admin-btn--accent">
                <x-icon.admin name="check" class="h-4 w-4" />
                <span>{{ $editing ? 'ذخیرهٔ تغییرات' : 'ساخت دسته' }}</span>
            </button>

            <a href="{{ route('admin.categories.index') }}" class="admin-btn admin-btn--quiet">انصراف</a>

            @if ($editing)
                <span class="admin-form-bar-spacer"></span>

                <button type="submit"
                        form="delete-category"
                        class="admin-btn admin-btn--danger"
                        data-confirm="«{{ $category->name }}» و همهٔ موارد داخلش برای همیشه حذف می‌شود.">
                    <x-icon.admin name="trash" class="h-4 w-4" />
                    <span>حذف دسته</span>
                </button>
            @endif
        </div>
    </form>

    @if ($editing)
        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" id="delete-category" hidden>
            @csrf
            @method('DELETE')
        </form>
    @endif
</x-layouts.admin>
