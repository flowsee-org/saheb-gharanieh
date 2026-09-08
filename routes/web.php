<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FeatureController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| The menu people read at the table
|--------------------------------------------------------------------------
*/

Route::get('/', HomeController::class)->name('home');

Route::get('/menu', MenuController::class)->name('menu');

// Deep link straight to a section, e.g. /menu/hot-drinks — the page still
// contains the full menu and simply scrolls to that section on load.
Route::get('/menu/{section}', MenuController::class)->name('menu.section');

/*
|--------------------------------------------------------------------------
| The café panel
|--------------------------------------------------------------------------
| Everything lives under /wp-admin. Categories and items bind on `id` here
| (`{category:id}`) because the panel is where a slug gets renamed, and a
| model must not disappear from under the form that is editing it.
*/

Route::prefix('wp-admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'create'])->name('login');
    Route::post('login', [AuthController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('login.store');

    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', [AuthController::class, 'destroy'])->name('logout');

        Route::get('/', DashboardController::class)->name('dashboard');

        /* ── Menu items ─────────────────────────────────────────────── */
        Route::prefix('items')->name('products.')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('create', [ProductController::class, 'create'])->name('create');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::post('bulk', [ProductController::class, 'bulk'])->name('bulk');
            Route::post('bulk-price', [ProductController::class, 'bulkPrice'])->name('bulk-price');

            Route::get('{product}/edit', [ProductController::class, 'edit'])->name('edit');
            Route::put('{product}', [ProductController::class, 'update'])->name('update');
            Route::delete('{product}', [ProductController::class, 'destroy'])->name('destroy');
            Route::patch('{product}/price', [ProductController::class, 'price'])->name('price');
            Route::patch('{product}/toggle', [ProductController::class, 'toggle'])->name('toggle');
            Route::patch('{product}/move', [ProductController::class, 'move'])->name('move');
            Route::delete('{product}/image', [ProductController::class, 'destroyImage'])->name('image.destroy');
        });

        /* ── Sections ───────────────────────────────────────────────── */
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('index');
            Route::get('create', [CategoryController::class, 'create'])->name('create');
            Route::post('/', [CategoryController::class, 'store'])->name('store');
            Route::post('reorder', [CategoryController::class, 'reorder'])->name('reorder');

            Route::get('{category:id}/edit', [CategoryController::class, 'edit'])->name('edit');
            Route::put('{category:id}', [CategoryController::class, 'update'])->name('update');
            Route::delete('{category:id}', [CategoryController::class, 'destroy'])->name('destroy');
            Route::patch('{category:id}/toggle', [CategoryController::class, 'toggle'])->name('toggle');
            Route::patch('{category:id}/move', [CategoryController::class, 'move'])->name('move');

            // Extras printed under a section (باقلوا، یخ، فویل …)
            Route::post('{category:id}/features', [FeatureController::class, 'store'])->name('features.store');
        });

        Route::name('features.')->group(function () {
            Route::put('features/{feature}', [FeatureController::class, 'update'])->name('update');
            Route::delete('features/{feature}', [FeatureController::class, 'destroy'])->name('destroy');
        });

        /* ── Site copy & account ────────────────────────────────────── */
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        Route::get('account', [AccountController::class, 'edit'])->name('account.edit');
        Route::put('account', [AccountController::class, 'update'])->name('account.update');
    });
});
