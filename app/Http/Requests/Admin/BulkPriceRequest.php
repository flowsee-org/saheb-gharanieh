<?php

namespace App\Http\Requests\Admin;

use App\Support\Persian;
use Illuminate\Validation\Rule;

/**
 * Bulk price adjustment — add a percentage or fixed amount to prices across
 * every item, or one category. The scope is a single `category_id` (empty
 * means "all items"), so this stays separate from the checkbox bulk flow.
 */
class BulkPriceRequest extends AdminRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'mode' => ['required', 'in:percentage,fixed'],
            'amount' => ['required', 'integer', 'min:1', 'max:999999999'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['amount' => Persian::amount($this->input('amount'))]);
    }

    public function categoryId(): ?int
    {
        $category = $this->input('category_id');

        return $category === null || $category === '' ? null : (int) $category;
    }

    public function isPercentage(): bool
    {
        return $this->validated('mode') === 'percentage';
    }

    public function isFixed(): bool
    {
        return $this->validated('mode') === 'fixed';
    }

    public function amount(): int
    {
        return (int) $this->validated('amount');
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'دسته',
            'mode' => 'نوع تغییر',
            'amount' => 'مقدار',
        ];
    }
}
