@php $editing = $product->exists; @endphp

<x-layouts.admin :title="$editing ? 'ویرایش مورد' : 'مورد جدید'"
                 :heading="$editing ? $product->name : 'مورد جدید'"
                 :subheading="$editing ? 'ویرایش مشخصات این مورد' : 'یک نوشیدنی یا طعم قلیان به منو اضافه کنید'">
    <x-slot:actions>
        <a href="{{ route('admin.products.index', ['category' => $product->category_id]) }}"
           class="admin-btn admin-btn--quiet">
            <x-icon.admin name="right" class="h-4 w-4" />
            <span>بازگشت به فهرست</span>
        </a>
    </x-slot:actions>

    <form method="POST"
          action="{{ $editing ? route('admin.products.update', $product) : route('admin.products.store') }}"
          enctype="multipart/form-data"
          class="admin-form">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="admin-form-grid">
            <div class="admin-form-col">
                <x-admin.card title="مشخصات" icon="items">
                    <x-admin.field label="نام مورد" name="name" required hint="همان چیزی که در منو دیده می‌شود.">
                        <input type="text" class="admin-input" id="name" name="name"
                               value="{{ old('name', $product->name) }}" required autofocus
                               maxlength="120">
                    </x-admin.field>

                    <x-admin.field label="نام لاتین" name="latin_name" hint="اختیاری — زیر نام فارسی چاپ می‌شود.">
                        <input type="text" class="admin-input latin-input" id="latin_name" name="latin_name"
                               value="{{ old('latin_name', $product->latin_name) }}" dir="ltr" maxlength="120">
                    </x-admin.field>

                    <x-admin.field label="دسته" name="category_id" required>
                        <select class="admin-select" id="category_id" name="category_id" required>
                            <option value="">انتخاب کنید…</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                        @selected((int) old('category_id', $product->category_id) === $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </x-admin.field>

                    <x-admin.field label="توضیح کوتاه" name="description"
                                   hint="اختیاری — یک جمله، مثلاً «با شیر بادام و دارچین».">
                        <textarea class="admin-input admin-textarea" id="description" name="description"
                                  rows="3" maxlength="400">{{ old('description', $product->description) }}</textarea>
                    </x-admin.field>

                    <x-admin.field label="قیمت (تومان)" name="price"
                                   hint="خالی بگذارید تا در منو جای قیمت خالی بماند.">
                        <span class="admin-price-wrap admin-price-wrap--wide">
                            <input type="text" class="admin-input" id="price" name="price"
                                   value="{{ old('price', $product->price ? \App\Support\Persian::number($product->price) : '') }}"
                                   inputmode="numeric" placeholder="۱۸۵٬۰۰۰">
                            <span class="admin-price-unit">تومان</span>
                        </span>
                    </x-admin.field>
                </x-admin.card>

                <x-admin.card title="نقش تزئینی" icon="sparkle"
                              subtitle="نقشی که کنار نام یا داخل جای تصویر دیده می‌شود">
                    <x-admin.glyph-picker name="glyph" :groups="$glyphGroups"
                                          :selected="$product->glyph" label="نقش این مورد" />
                </x-admin.card>
            </div>

            <div class="admin-form-col">
                <x-admin.card title="تصویر" icon="image">
                    <x-admin.image-field :product="$product" />
                </x-admin.card>

                <x-admin.card title="نمایش در منو" icon="eye">
                    <x-admin.switch name="is_active" label="نمایش در منو"
                                    hint="خاموش کنید تا بدون حذف شدن، از منو پنهان شود."
                                    :checked="(bool) $product->is_active" />

                    <x-admin.switch name="is_available" label="موجود است"
                                    hint="خاموش کنید تا روی مورد «موقتاً تمام شد» بخورد."
                                    :checked="(bool) $product->is_available" />

                    <x-admin.switch name="is_featured" label="مورد ویژه"
                                    hint="برای برجسته‌کردن یک پیشنهاد خاص."
                                    :checked="(bool) $product->is_featured" />

                    <x-admin.field label="ترتیب در دسته" name="sort_order"
                                   hint="عدد کوچک‌تر بالاتر می‌آید. با دکمه‌های ↑ ↓ در فهرست هم قابل تغییر است.">
                        <input type="text" class="admin-input admin-input--narrow" id="sort_order" name="sort_order"
                               value="{{ old('sort_order', \App\Support\Persian::digits($product->sort_order ?? 0)) }}"
                               inputmode="numeric">
                    </x-admin.field>
                </x-admin.card>
            </div>
        </div>

        <div class="admin-form-bar">
            <button type="submit" class="admin-btn admin-btn--accent">
                <x-icon.admin name="check" class="h-4 w-4" />
                <span>{{ $editing ? 'ذخیرهٔ تغییرات' : 'افزودن به منو' }}</span>
            </button>

            <a href="{{ route('admin.products.index', ['category' => $product->category_id]) }}"
               class="admin-btn admin-btn--quiet">انصراف</a>

            @if ($editing)
                <span class="admin-form-bar-spacer"></span>

                {{-- Its own form so the delete button never posts the edit fields. --}}
                <button type="submit"
                        form="delete-product"
                        class="admin-btn admin-btn--danger"
                        data-confirm="«{{ $product->name }}» و تصویرش برای همیشه حذف می‌شود.">
                    <x-icon.admin name="trash" class="h-4 w-4" />
                    <span>حذف مورد</span>
                </button>
            @endif
        </div>
    </form>

    @if ($editing)
        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" id="delete-product" hidden>
            @csrf
            @method('DELETE')
        </form>
    @endif
</x-layouts.admin>
