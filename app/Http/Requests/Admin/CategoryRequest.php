<?php

namespace App\Http\Requests\Admin;

use App\Enums\CategoryLayout;
use App\Support\Glyph;
use App\Support\Persian;
use Illuminate\Validation\Rule;

/** Add and edit a menu section. */
class CategoryRequest extends AdminRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'short_name' => ['nullable', 'string', 'max:40'],
            'latin_name' => ['nullable', 'string', 'max:80'],
            'subtitle' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:600'],
            'slug' => [
                'nullable', 'string', 'max:80', 'alpha_dash',
                Rule::unique('categories', 'slug')->ignore($this->route('category')),
            ],
            'kind' => ['required', 'string', 'max:40'],
            'layout' => ['required', Rule::enum(CategoryLayout::class)],
            'glyph' => ['nullable', Rule::in(Glyph::keys())],

            // One service price for the whole section — how the hookah sections work.
            'price' => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'price_note' => ['nullable', 'string', 'max:120'],

            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['boolean'],

            // Landing-page card. `card_order` is the real column; the form shows a switch.
            'show_on_landing' => ['boolean'],
            'card_title' => ['nullable', 'string', 'max:80'],
            'card_subtitle' => ['nullable', 'string', 'max:160'],
            'card_latin' => ['nullable', 'string', 'max:80'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->mergeBooleans(['is_active', 'show_on_landing']);

        $this->merge([
            'price' => Persian::amount($this->input('price')),
            'sort_order' => Persian::amount($this->input('sort_order')),
            // Blank slug means "derive it from the name" — the model's saving() hook does that.
            'slug' => $this->filled('slug')
                ? Persian::western(trim((string) $this->input('slug')))
                : null,
            'glyph' => $this->filled('glyph') ? $this->input('glyph') : null,
        ]);
    }

    /**
     * Columns to save. `card_order` is resolved by the controller, which is the
     * only place that can see the other categories' positions.
     *
     * @return array<string, mixed>
     */
    public function attributesToSave(): array
    {
        $values = [
            ...$this->safe()->only([
                'name', 'short_name', 'latin_name', 'subtitle', 'description',
                'kind', 'layout', 'glyph', 'price', 'price_note', 'is_active',
                'card_title', 'card_subtitle', 'card_latin',
            ]),
            'sort_order' => $this->integer('sort_order'),
        ];

        // An empty slug is left out entirely so the model can generate one.
        if ($this->filled('slug')) {
            $values['slug'] = $this->string('slug')->toString();
        }

        return $values;
    }

    public function showsOnLanding(): bool
    {
        return $this->boolean('show_on_landing');
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'نام دسته',
            'short_name' => 'نام کوتاه',
            'latin_name' => 'نام لاتین',
            'subtitle' => 'زیرعنوان',
            'description' => 'توضیح',
            'slug' => 'نشانی (slug)',
            'kind' => 'نوع',
            'layout' => 'چیدمان',
            'glyph' => 'نقش',
            'price' => 'قیمت سرویس',
            'price_note' => 'توضیح قیمت',
            'sort_order' => 'ترتیب',
            'card_title' => 'عنوان کارت',
            'card_subtitle' => 'زیرعنوان کارت',
            'card_latin' => 'نام لاتین کارت',
        ];
    }
}
