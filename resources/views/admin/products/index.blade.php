@php
    $statuses = [
        'all' => 'همه',
        'active' => 'در حال نمایش',
        'hidden' => 'پنهان',
        'sold-out' => 'تمام شده',
        'no-price' => 'بدون قیمت',
        'no-image' => 'بدون تصویر',
    ];
@endphp

<x-layouts.admin title="موارد منو" heading="موارد منو"
                 :subheading="'در مجموع '.\App\Support\Persian::digits($products->total()).' مورد'">
    <x-slot:actions>
        <a href="{{ route('admin.products.create', ['category' => $filters['category']]) }}"
           class="admin-btn admin-btn--accent">
            <x-icon.admin name="plus" class="h-4 w-4" />
            <span>مورد جدید</span>
        </a>
    </x-slot:actions>

    {{-- Search and filters. A GET form, so every result set has its own URL and
         the owner can bookmark "everything without a price". --}}
    <form method="GET" action="{{ route('admin.products.index') }}" class="admin-filters" data-filters>
        <div class="admin-search">
            <x-icon.admin name="search" class="admin-search-icon" />
            <input type="search"
                   name="q"
                   value="{{ $filters['q'] }}"
                   class="admin-input admin-search-input"
                   placeholder="جست‌وجوی نام مورد…"
                   enterkeyhint="search"
                   aria-label="جست‌وجو">

            @if ($filters['q'])
                <a href="{{ route('admin.products.index', array_filter(['category' => $filters['category'], 'status' => $filters['status'] === 'all' ? null : $filters['status']])) }}"
                   class="admin-search-clear" aria-label="پاک کردن جست‌وجو">
                    <x-icon.admin name="close" class="h-3.5 w-3.5" />
                </a>
            @endif
        </div>

        <div class="admin-filter-row">
            <select name="category" class="admin-select" aria-label="دسته" data-filter-submit>
                <option value="">همهٔ دسته‌ها</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($filters['category'] === $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            <select name="status" class="admin-select" aria-label="وضعیت" data-filter-submit>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="sort" class="admin-select" aria-label="ترتیب" data-filter-submit>
                <option value="menu" @selected($filters['sort'] === 'menu')>ترتیب منو</option>
                <option value="newest" @selected($filters['sort'] === 'newest')>جدیدترین</option>
            </select>

            {{-- Only needed when JS is off; the selects submit themselves otherwise. --}}
            <button type="submit" class="admin-btn admin-btn--ghost admin-filter-apply">
                <x-icon.admin name="filter" class="h-4 w-4" />
                <span>اعمال</span>
            </button>
        </div>
    </form>

    {{-- The bulk form holds no rows: the row checkboxes join it by `form=` so the
         per-item forms below can stay separate elements instead of nesting. --}}
    <form method="POST" action="{{ route('admin.products.bulk') }}" id="bulk-form"
          class="admin-bulk" data-bulk hidden>
        @csrf

        <p class="admin-bulk-count">
            <span data-bulk-count>۰</span> مورد انتخاب شده
        </p>

        <div class="admin-bulk-actions">
            <select name="category_id" class="admin-select admin-bulk-select" aria-label="انتقال به دسته"
                    data-bulk-category>
                <option value="">انتقال به دسته…</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>

            <button type="submit" name="action" value="category" class="admin-btn admin-btn--ghost">
                <x-icon.admin name="categories" class="h-4 w-4" />
                <span>انتقال</span>
            </button>

            <button type="submit" name="action" value="activate" class="admin-btn admin-btn--quiet">
                <x-icon.admin name="eye" class="h-4 w-4" />
                <span>نمایش</span>
            </button>

            <button type="submit" name="action" value="deactivate" class="admin-btn admin-btn--quiet">
                <x-icon.admin name="hidden" class="h-4 w-4" />
                <span>پنهان</span>
            </button>

            <button type="submit" name="action" value="delete" class="admin-btn admin-btn--danger"
                    data-confirm="موارد انتخاب‌شده و تصویرشان برای همیشه حذف می‌شوند.">
                <x-icon.admin name="trash" class="h-4 w-4" />
                <span>حذف</span>
            </button>

            <button type="button" class="admin-btn admin-btn--quiet" data-bulk-clear>
                <x-icon.admin name="close" class="h-4 w-4" />
                <span>لغو انتخاب</span>
            </button>
        </div>
    </form>

    @if ($products->isEmpty())
        <x-admin.card>
            <x-admin.empty icon="search"
                           title="موردی پیدا نشد"
                           text="جست‌وجو یا فیلترها را تغییر دهید، یا یک مورد جدید بسازید.">
                <x-slot:action>
                    <a href="{{ route('admin.products.index') }}" class="admin-btn admin-btn--ghost">
                        نمایش همهٔ موارد
                    </a>
                </x-slot:action>
            </x-admin.empty>
        </x-admin.card>
    @else
        <div class="admin-items" data-items>
            <label class="admin-select-all">
                <input type="checkbox" data-bulk-all aria-label="انتخاب همهٔ موارد این صفحه">
                <span>انتخاب همهٔ این صفحه</span>
            </label>

            @foreach ($products as $product)
                <article class="admin-item @unless ($product->is_active) admin-item--off @endunless">
                    <label class="admin-item-check">
                        <input type="checkbox" name="ids[]" value="{{ $product->id }}" form="bulk-form"
                               data-bulk-item aria-label="انتخاب {{ $product->name }}">
                    </label>

                    <span class="admin-item-thumb">
                        @if ($product->imageUrl())
                            <img src="{{ $product->imageUrl() }}" alt="" loading="lazy">
                        @else
                            <x-icon.glyph :name="$product->glyphKey()" class="admin-item-thumb-glyph" />
                        @endif
                    </span>

                    <div class="admin-item-body">
                        <div class="admin-item-titles">
                            <a href="{{ route('admin.products.edit', $product) }}" class="admin-item-name">
                                {{ $product->name }}
                            </a>

                            <span class="admin-item-meta">
                                {{ $product->category?->name }}
                                @if ($product->latin_name)
                                    <span class="admin-dot" aria-hidden="true"></span>
                                    <span class="latin admin-item-latin">{{ $product->latin_name }}</span>
                                @endif
                            </span>
                        </div>

                        <div class="admin-item-tags">
                            @unless ($product->is_active)
                                <span class="admin-tag admin-tag--mute">پنهان</span>
                            @endunless
                            @unless ($product->is_available)
                                <span class="admin-tag admin-tag--warn">تمام شده</span>
                            @endunless
                            @if ($product->is_featured)
                                <span class="admin-tag admin-tag--accent">ویژه</span>
                            @endif
                            @unless ($product->hasImage())
                                <span class="admin-tag admin-tag--ghost">بدون تصویر</span>
                            @endunless
                        </div>

                        {{-- Quick price: the edit the owner makes most often. --}}
                        <form method="POST" action="{{ route('admin.products.price', $product) }}"
                              class="admin-price-quick" data-quick-price>
                            @csrf
                            @method('PATCH')

                            <span class="admin-price-wrap">
                                <input type="text"
                                       name="price"
                                       value="{{ $product->price ? \App\Support\Persian::number($product->price) : '' }}"
                                       class="admin-input admin-price-input"
                                       inputmode="numeric"
                                       placeholder="قیمت"
                                       aria-label="قیمت {{ $product->name }}"
                                       data-price-input>
                                <span class="admin-price-unit">تومان</span>
                            </span>

                            <button type="submit" class="admin-icon-btn admin-icon-btn--accent" aria-label="ذخیرهٔ قیمت">
                                <x-icon.admin name="check" class="h-4 w-4" />
                            </button>
                        </form>
                    </div>

                    <div class="admin-item-actions">
                        @if ($filters['sort'] === 'menu')
                            <form method="POST" action="{{ route('admin.products.move', $product) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="direction" value="up">
                                <button type="submit" class="admin-icon-btn" aria-label="یک پله بالاتر">
                                    <x-icon.admin name="up" class="h-4 w-4" />
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.products.move', $product) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="direction" value="down">
                                <button type="submit" class="admin-icon-btn" aria-label="یک پله پایین‌تر">
                                    <x-icon.admin name="down" class="h-4 w-4" />
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('admin.products.toggle', $product) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="admin-icon-btn"
                                    aria-label="{{ $product->is_active ? 'پنهان کردن' : 'نمایش دادن' }}"
                                    title="{{ $product->is_active ? 'پنهان کردن' : 'نمایش دادن' }}">
                                <x-icon.admin :name="$product->is_active ? 'eye' : 'hidden'" class="h-4 w-4" />
                            </button>
                        </form>

                        <a href="{{ route('admin.products.edit', $product) }}" class="admin-icon-btn"
                           aria-label="ویرایش {{ $product->name }}" title="ویرایش">
                            <x-icon.admin name="edit" class="h-4 w-4" />
                        </a>

                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="admin-icon-btn admin-icon-btn--danger"
                                    data-confirm="«{{ $product->name }}» و تصویرش برای همیشه حذف می‌شود."
                                    aria-label="حذف {{ $product->name }}" title="حذف">
                                <x-icon.admin name="trash" class="h-4 w-4" />
                            </button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>

        <x-admin.pagination :paginator="$products" />
    @endif
</x-layouts.admin>
