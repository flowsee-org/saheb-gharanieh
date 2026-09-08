<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkPriceRequest;
use App\Http\Requests\Admin\BulkProductRequest;
use App\Http\Requests\Admin\ProductRequest;
use App\Http\Requests\Admin\QuickPriceRequest;
use App\Models\Category;
use App\Models\Product;
use App\Support\Glyph;
use App\Support\ImageStore;
use App\Support\Persian;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        $products = Product::query()
            ->with('category:id,name,slug')
            ->when($filters['q'] !== null, function ($query) use ($filters) {
                $term = '%'.$filters['q'].'%';

                $query->where(fn ($inner) => $inner
                    ->where('name', 'like', $term)
                    ->orWhere('latin_name', 'like', $term)
                    ->orWhere('description', 'like', $term));
            })
            ->when($filters['category'] !== null, fn ($query) => $query->where('category_id', $filters['category']))
            ->when($filters['status'] === 'active', fn ($query) => $query->where('is_active', true))
            ->when($filters['status'] === 'hidden', fn ($query) => $query->where('is_active', false))
            ->when($filters['status'] === 'sold-out', fn ($query) => $query->where('is_available', false))
            ->when($filters['status'] === 'no-price', fn ($query) => $query->whereNull('price'))
            ->when($filters['status'] === 'no-image', fn ($query) => $query->whereNull('image_path'))
            ->when(
                $filters['sort'] === 'newest',
                fn ($query) => $query->latest('id'),
                fn ($query) => $query->orderBy('category_id')->orderBy('sort_order')->orderBy('id'),
            )
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::query()->ordered()->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View
    {
        // Pre-select the section the owner came from, and park the new item last.
        $category = Category::query()->find($request->integer('category'));

        $product = new Product([
            'category_id' => $category?->id,
            'is_active' => true,
            'is_available' => true,
            'sort_order' => $category ? $this->nextSortOrder($category) : 0,
        ]);

        return $this->form($product);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $product = new Product($request->attributesToSave());

        if ($request->hasFile('image')) {
            $product->image_path = ImageStore::put($request->file('image'));
        }

        $product->save();

        return redirect()
            ->route('admin.products.index', ['category' => $product->category_id])
            ->with('status', "«{$product->name}» اضافه شد.");
    }

    public function edit(Product $product): View
    {
        return $this->form($product);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->fill($request->attributesToSave());

        if ($request->hasFile('image')) {
            $product->image_path = ImageStore::replace($product->image_path, $request->file('image'));
        } elseif ($request->boolean('remove_image')) {
            ImageStore::forget($product->image_path);
            $product->image_path = null;
        }

        $product->save();

        return redirect()
            ->route('admin.products.index', ['category' => $product->category_id])
            ->with('status', "«{$product->name}» ذخیره شد.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Coming from this item's own editor there is nothing to go back to, so
        // land on its section's list; from the list, keep the filters in place.
        $fallback = route('admin.products.index', ['category' => $product->category_id]);
        $origin = url()->previous() === route('admin.products.edit', $product) ? $fallback : url()->previous();

        ImageStore::forget($product->image_path);
        $product->delete();

        return redirect()->to($origin)->with('status', "«{$product->name}» حذف شد.");
    }

    /** The inline price box on the list. */
    public function price(QuickPriceRequest $request, Product $product): RedirectResponse
    {
        $product->update(['price' => $request->price()]);

        return back()->with('status', $product->hasPrice()
            ? "قیمت «{$product->name}» شد ".Persian::price($product->price)
            : "قیمت «{$product->name}» پاک شد.");
    }

    public function toggle(Product $product): RedirectResponse
    {
        $product->update(['is_active' => ! $product->is_active]);

        return back()->with('status', $product->is_active
            ? "«{$product->name}» در منو نمایش داده می‌شود."
            : "«{$product->name}» از منو پنهان شد.");
    }

    /** Swap an item with its neighbour inside the same section. */
    public function move(Request $request, Product $product): RedirectResponse
    {
        $up = $request->input('direction') !== 'down';

        $moved = DB::transaction(function () use ($product, $up) {
            // Renumber first: items arrive from the seeder sharing sort_order 0,
            // where ties break on id and swapping two equal values does nothing.
            $this->normaliseOrder($product->category_id);
            $product->refresh();

            $neighbour = Product::query()
                ->where('category_id', $product->category_id)
                ->where('sort_order', $up ? $product->sort_order - 1 : $product->sort_order + 1)
                ->first();

            if (! $neighbour) {
                return false;
            }

            [$product->sort_order, $neighbour->sort_order] = [$neighbour->sort_order, $product->sort_order];

            $product->save();
            $neighbour->save();

            return true;
        });

        return back()->with('status', $moved
            ? "جای «{$product->name}» عوض شد."
            : sprintf('«%s» همین حالا در %s فهرست است.', $product->name, $up ? 'ابتدای' : 'انتهای'));
    }

    public function destroyImage(Product $product): RedirectResponse
    {
        ImageStore::forget($product->image_path);
        $product->update(['image_path' => null]);

        return back()->with('status', "تصویر «{$product->name}» حذف شد.");
    }

    public function bulk(BulkProductRequest $request): RedirectResponse
    {
        $ids = $request->ids();
        $count = Persian::digits(count($ids));
        $selected = Product::query()->whereKey($ids);

        $status = match ($request->input('action')) {
            'delete' => $this->deleteAll($ids, "{$count} مورد حذف شد."),

            'category' => $this->moveAll($selected, $request->integer('category_id'), $count),

            'activate' => $this->set($selected, ['is_active' => true], "{$count} مورد در منو نمایش داده می‌شود."),
            'deactivate' => $this->set($selected, ['is_active' => false], "{$count} مورد از منو پنهان شد."),
            'available' => $this->set($selected, ['is_available' => true], "{$count} مورد موجود شد."),
            'unavailable' => $this->set($selected, ['is_available' => false], "{$count} مورد «تمام شد» علامت خورد."),
        };

        return back()->with('status', $status);
    }

    /**
     * Apply a percentage or fixed-price change across every item, or the ones
     * in one category. Items without a price are skipped — there is nothing
     * to scale — so the count only reflects what actually changed.
     */
    public function bulkPrice(BulkPriceRequest $request): RedirectResponse
    {
        $categoryId = $request->categoryId();

        $scope = $categoryId !== null
            ? "«".Category::query()->findOrFail($categoryId)->name."»"
            : 'همهٔ منو';

        $updated = 0;

        DB::transaction(function () use ($request, $categoryId, &$updated) {
            Product::query()
                ->when($categoryId !== null, fn ($query) => $query->where('category_id', $categoryId))
                ->whereNotNull('price')
                ->get()
                ->each(function (Product $product) use ($request, &$updated) {
                    $price = $request->isPercentage()
                        ? (int) round($product->price * (100 + $request->amount()) / 100)
                        : $product->price + $request->amount();

                    if ($price < 0) {
                        $price = 0;
                    }

                    if ($price !== $product->price) {
                        $product->updateQuietly(['price' => $price]);
                        $updated++;
                    }
                });
        });

        $count = Persian::digits($updated);
        $amount = Persian::digits($request->amount());

        $status = $request->isPercentage()
            ? "قیمت {$count} مورد در {$scope} {$amount}٪ افزایش یافت."
            : "به قیمت {$count} مورد در {$scope} {$amount} تومان اضافه شد.";

        return back()->with('status', $status);
    }

    /* ─────────────────────────────────────────────────────────────────── */

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Product>  $selected
     * @param  array<string, mixed>  $values
     */
    private function set(Builder $selected, array $values, string $status): string
    {
        $selected->update($values);

        return $status;
    }

    /** @param  \Illuminate\Database\Eloquent\Builder<Product>  $selected */
    private function moveAll(Builder $selected, int $categoryId, string $count): string
    {
        $category = Category::query()->findOrFail($categoryId);

        $selected->update(['category_id' => $category->id]);

        return "{$count} مورد به «{$category->name}» منتقل شد.";
    }

    /**
     * Delete items along with the files they own.
     *
     * @param  array<int, int>  $ids
     */
    private function deleteAll(array $ids, string $status): string
    {
        foreach (Product::query()->whereKey($ids)->get() as $product) {
            ImageStore::forget($product->image_path);
        }

        Product::query()->whereKey($ids)->delete();

        return $status;
    }

    private function form(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::query()->ordered()->get(['id', 'name']),
            'glyphGroups' => Glyph::GROUPS,
        ]);
    }

    /** Where a brand-new item lands: after everything already in the section. */
    private function nextSortOrder(Category $category): int
    {
        return (int) Product::query()->where('category_id', $category->id)->max('sort_order') + 1;
    }

    /** Rewrite a section's sort_order as 1, 2, 3 … in its current visible order. */
    private function normaliseOrder(int $categoryId): void
    {
        Product::query()
            ->where('category_id', $categoryId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id'])
            ->each(fn (Product $item, int $index) => $item->updateQuietly(['sort_order' => $index + 1]));
    }

    /**
     * The list's search box and dropdowns, normalised once so the query, the
     * view and the pagination links all read the same values.
     *
     * @return array{q: ?string, category: ?int, status: string, sort: string}
     */
    private function filters(Request $request): array
    {
        $term = trim((string) $request->input('q'));

        return [
            'q' => $term === '' ? null : $term,
            'category' => $request->filled('category') ? $request->integer('category') : null,
            'status' => in_array($request->input('status'), ['active', 'hidden', 'sold-out', 'no-price', 'no-image'], true)
                ? $request->input('status')
                : 'all',
            'sort' => $request->input('sort') === 'newest' ? 'newest' : 'menu',
        ];
    }
}
