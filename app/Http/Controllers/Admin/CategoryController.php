<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CategoryLayout;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Requests\Admin\ReorderRequest;
use App\Models\Category;
use App\Support\Glyph;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::query()
                ->ordered()
                ->with(['features' => fn ($query) => $query->orderBy('sort_order')])
                ->withCount([
                    'products',
                    'products as active_products_count' => fn ($query) => $query->where('is_active', true),
                ])
                ->get(),
            'glyphGroups' => Glyph::GROUPS,
        ]);
    }

    public function create(): View
    {
        return $this->form(new Category([
            'kind' => 'drink',
            'layout' => CategoryLayout::Grid,
            'is_active' => true,
            'sort_order' => (int) Category::query()->max('sort_order') + 1,
        ]));
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $category = new Category($request->attributesToSave());
        $category->card_order = $this->cardOrderFor($category, $request->showsOnLanding());
        $category->save();

        return redirect()
            ->route('admin.categories.index')
            ->with('status', "دسته «{$category->name}» ساخته شد.");
    }

    public function edit(Category $category): View
    {
        return $this->form($category);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->fill($request->attributesToSave());
        $category->card_order = $this->cardOrderFor($category, $request->showsOnLanding());
        $category->save();

        return redirect()
            ->route('admin.categories.index')
            ->with('status', "دسته «{$category->name}» ذخیره شد.");
    }

    /**
     * Deleting a section takes its items with it (the products table cascades),
     * so the confirmation dialog in the view spells out how many that is.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $name = $category->name;
        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('status', "دسته «{$name}» و موارد داخلش حذف شد.");
    }

    public function toggle(Category $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return back()->with('status', $category->is_active
            ? "«{$category->name}» در منو نمایش داده می‌شود."
            : "«{$category->name}» از منو پنهان شد.");
    }

    /** ↑ / ↓ on a phone, where dragging a whole row is fiddly. */
    public function move(Request $request, Category $category): RedirectResponse
    {
        $up = $request->input('direction') !== 'down';

        $moved = DB::transaction(function () use ($category, $up) {
            $this->normaliseOrder();
            $category->refresh();

            $neighbour = Category::query()
                ->where('sort_order', $up ? $category->sort_order - 1 : $category->sort_order + 1)
                ->first();

            if (! $neighbour) {
                return false;
            }

            [$category->sort_order, $neighbour->sort_order] = [$neighbour->sort_order, $category->sort_order];

            $category->save();
            $neighbour->save();

            return true;
        });

        return back()->with('status', $moved
            ? "جای «{$category->name}» عوض شد."
            : sprintf('«%s» همین حالا %s منو است.', $category->name, $up ? 'اولِ' : 'آخرِ'));
    }

    /** Drag-and-drop on a wide screen: the whole new order arrives at once. */
    public function reorder(ReorderRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            foreach ($request->ids() as $index => $id) {
                Category::query()->whereKey($id)->update(['sort_order' => $index + 1]);
            }
        });

        return back()->with('status', 'ترتیب دسته‌ها ذخیره شد.');
    }

    /* ─────────────────────────────────────────────────────────────────── */

    private function form(Category $category): View
    {
        return view('admin.categories.form', [
            'category' => $category,
            'layouts' => CategoryLayout::cases(),
            'glyphGroups' => Glyph::GROUPS,
        ]);
    }

    /**
     * `card_order` doubles as the landing-page flag: NULL means "not on the
     * landing page", any number means "show it, in this position".
     */
    private function cardOrderFor(Category $category, bool $onLanding): ?int
    {
        if (! $onLanding) {
            return null;
        }

        return $category->card_order ?? (int) Category::query()->max('card_order') + 1;
    }

    /** Rewrite every sort_order as 1, 2, 3 … in the current visible order. */
    private function normaliseOrder(): void
    {
        Category::query()
            ->ordered()
            ->get(['id'])
            ->each(fn (Category $item, int $index) => $item->updateQuietly(['sort_order' => $index + 1]));
    }
}
