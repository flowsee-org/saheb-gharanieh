<x-layouts.admin title="دسته‌ها" heading="دسته‌های منو"
                 subheading="ترتیب، نمایش و محتوای بخش‌های منو">
    <x-slot:actions>
        <a href="{{ route('admin.categories.create') }}" class="admin-btn admin-btn--accent">
            <x-icon.admin name="plus" class="h-4 w-4" />
            <span>دستهٔ جدید</span>
        </a>
    </x-slot:actions>

    @if ($categories->isEmpty())
        <x-admin.card>
            <x-admin.empty icon="categories"
                           title="هنوز دسته‌ای ساخته نشده"
                           text="منو از دسته‌ها ساخته می‌شود: نوشیدنی گرم، نوشیدنی سرد، قلیان…">
                <x-slot:action>
                    <a href="{{ route('admin.categories.create') }}" class="admin-btn admin-btn--accent">
                        <x-icon.admin name="plus" class="h-4 w-4" />
                        <span>ساخت اولین دسته</span>
                    </a>
                </x-slot:action>
            </x-admin.empty>
        </x-admin.card>
    @else
        <p class="admin-note">
            <x-icon.admin name="grip" class="h-4 w-4 shrink-0" />
            <span>
                ترتیب دسته‌ها همان ترتیب منو است. روی کامپیوتر می‌توانید ردیف‌ها را بکشید و جابه‌جا کنید،
                روی گوشی از دکمه‌های ↑ و ↓ استفاده کنید.
            </span>
        </p>

        {{-- Drag-and-drop posts the whole new order at once; the ↑/↓ forms below
             swap a single pair. Both end up in the same sort_order column. --}}
        <form method="POST" action="{{ route('admin.categories.reorder') }}" id="reorder-form" hidden>
            @csrf
        </form>

        <div class="admin-cats" data-reorder data-reorder-form="reorder-form">
            @foreach ($categories as $category)
                <article class="admin-cat @unless ($category->is_active) admin-cat--off @endunless"
                         data-reorder-item data-id="{{ $category->id }}">
                    <span class="admin-cat-grip" data-reorder-handle aria-hidden="true">
                        <x-icon.admin name="grip" class="h-4 w-4" />
                    </span>

                    <span class="admin-cat-glyph">
                        <x-icon.glyph :name="\App\Support\Glyph::forCategory($category)" class="h-6 w-6" />
                    </span>

                    <div class="admin-cat-body">
                        <div class="admin-cat-titles">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="admin-cat-name">
                                {{ $category->name }}
                            </a>

                            <span class="admin-cat-meta">
                                <span class="latin admin-cat-slug" dir="ltr">{{ $category->slug }}</span>
                                <span class="admin-dot" aria-hidden="true"></span>
                                {{ $category->kind->label() }}
                                <span class="admin-dot" aria-hidden="true"></span>
                                {{ $category->layout->label() }}
                            </span>
                        </div>

                        <div class="admin-cat-tags">
                            <a href="{{ route('admin.products.index', ['category' => $category->id]) }}"
                               class="admin-tag admin-tag--count">
                                @fa($category->products_count) مورد
                            </a>

                            @unless ($category->is_active)
                                <span class="admin-tag admin-tag--mute">پنهان</span>
                            @endunless

                            @if ($category->card_order !== null)
                                <span class="admin-tag admin-tag--accent">در صفحهٔ اصلی</span>
                            @endif

                            @if ($category->price)
                                <span class="admin-tag admin-tag--ghost">@price($category->price)</span>
                            @endif
                        </div>

                        {{-- Section extras: «باقلوا»، «یخ»، «فویل». Managed here rather
                             than in the category form — they are a list, not a field. --}}
                        <details class="admin-features">
                            <summary class="admin-features-summary">
                                <x-icon.admin name="sparkle" class="h-3.5 w-3.5" />
                                <span>همراه سرویس (@fa($category->features->count()))</span>
                                <x-icon.admin name="down" class="admin-features-chevron" />
                            </summary>

                            <div class="admin-features-body">
                                @foreach ($category->features as $feature)
                                    <form method="POST" action="{{ route('admin.features.update', $feature) }}"
                                          class="admin-feature">
                                        @csrf @method('PUT')

                                        <span class="admin-feature-glyph">
                                            <x-icon.glyph :name="$feature->glyph" class="h-4 w-4" />
                                        </span>

                                        <input type="text" name="name" value="{{ $feature->name }}"
                                               class="admin-input admin-input--slim"
                                               aria-label="نام {{ $feature->name }}" maxlength="80">

                                        <select name="glyph" class="admin-select admin-select--slim"
                                                aria-label="نقش {{ $feature->name }}">
                                            <option value="">بدون نقش</option>
                                            @foreach ($glyphGroups as $groupLabel => $glyphs)
                                                <optgroup label="{{ $groupLabel }}">
                                                    @foreach ($glyphs as $key => $glyphLabel)
                                                        <option value="{{ $key }}" @selected($feature->glyph === $key)>
                                                            {{ $glyphLabel }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>

                                        <button type="submit" class="admin-icon-btn admin-icon-btn--accent"
                                                aria-label="ذخیرهٔ {{ $feature->name }}">
                                            <x-icon.admin name="check" class="h-4 w-4" />
                                        </button>

                                        <button type="submit"
                                                form="feature-delete-{{ $feature->id }}"
                                                class="admin-icon-btn admin-icon-btn--danger"
                                                data-confirm="«{{ $feature->name }}» از این بخش حذف می‌شود."
                                                aria-label="حذف {{ $feature->name }}">
                                            <x-icon.admin name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.features.destroy', $feature) }}"
                                          id="feature-delete-{{ $feature->id }}" hidden>
                                        @csrf @method('DELETE')
                                    </form>
                                @endforeach

                                <form method="POST"
                                      action="{{ route('admin.categories.features.store', $category) }}"
                                      class="admin-feature admin-feature--new">
                                    @csrf

                                    <span class="admin-feature-glyph">
                                        <x-icon.admin name="plus" class="h-4 w-4" />
                                    </span>

                                    <input type="text" name="name" class="admin-input admin-input--slim"
                                           placeholder="مورد تازه، مثلاً «زغال اضافه»"
                                           aria-label="نام مورد تازه" maxlength="80">

                                    <select name="glyph" class="admin-select admin-select--slim"
                                            aria-label="نقش مورد تازه">
                                        <option value="">بدون نقش</option>
                                        @foreach ($glyphGroups as $groupLabel => $glyphs)
                                            <optgroup label="{{ $groupLabel }}">
                                                @foreach ($glyphs as $key => $glyphLabel)
                                                    <option value="{{ $key }}">{{ $glyphLabel }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>

                                    <button type="submit" class="admin-icon-btn admin-icon-btn--accent"
                                            aria-label="افزودن">
                                        <x-icon.admin name="plus" class="h-4 w-4" />
                                    </button>
                                </form>
                            </div>
                        </details>
                    </div>

                    <div class="admin-cat-actions">
                        <form method="POST" action="{{ route('admin.categories.move', $category) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="direction" value="up">
                            <button type="submit" class="admin-icon-btn" aria-label="یک پله بالاتر">
                                <x-icon.admin name="up" class="h-4 w-4" />
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.categories.move', $category) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="direction" value="down">
                            <button type="submit" class="admin-icon-btn" aria-label="یک پله پایین‌تر">
                                <x-icon.admin name="down" class="h-4 w-4" />
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.categories.toggle', $category) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="admin-icon-btn"
                                    aria-label="{{ $category->is_active ? 'پنهان کردن' : 'نمایش دادن' }}"
                                    title="{{ $category->is_active ? 'پنهان کردن' : 'نمایش دادن' }}">
                                <x-icon.admin :name="$category->is_active ? 'eye' : 'hidden'" class="h-4 w-4" />
                            </button>
                        </form>

                        <a href="{{ route('admin.categories.edit', $category) }}" class="admin-icon-btn"
                           aria-label="ویرایش {{ $category->name }}" title="ویرایش">
                            <x-icon.admin name="edit" class="h-4 w-4" />
                        </a>

                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="admin-icon-btn admin-icon-btn--danger"
                                    data-confirm="«{{ $category->name }}» همراه با @fa($category->products_count) مورد داخلش برای همیشه حذف می‌شود."
                                    aria-label="حذف {{ $category->name }}" title="حذف">
                                <x-icon.admin name="trash" class="h-4 w-4" />
                            </button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</x-layouts.admin>
