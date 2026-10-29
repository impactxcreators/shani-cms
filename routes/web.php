<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OutboundController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/search', [SearchController::class, 'index'])->name('search');

Route::get('/go/{type}/{id}', [OutboundController::class, 'go'])
    ->where('type', 'coupon|deal|store|product')->where('id', '[0-9]+')->name('go');
Route::get('/ad/{id}', [App\Http\Controllers\AdvertController::class, 'click'])->where('id', '[0-9]+')->name('ad.click');

Route::get('/coupons', [StoreController::class, 'coupons'])->name('coupons.index');
Route::get('/deals', [StoreController::class, 'deals'])->name('deals.index');
Route::get('/best', [PageController::class, 'best'])->name('roundups.index');
Route::get('/store', [StoreController::class, 'index'])->name('store.index');
Route::get('/categories', [CategoryController::class, 'index'])->name('category.index');
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/product-category/{slug}', [App\Http\Controllers\ProductCategoryController::class, 'show'])->name('product-category.show');
Route::get('/store/{slug}', [StoreController::class, 'show'])->name('store.show');

// Blog
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/category/{slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/author/{slug}', [BlogController::class, 'author'])->name('blog.author');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::post('/blog/{slug}/comment', [BlogController::class, 'comment'])
    ->middleware('throttle:6,1')->name('blog.comment');


// SEO endpoints
Route::get('/sitemap.xml', [App\Http\Controllers\SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/sitemap/html', [App\Http\Controllers\SeoController::class, 'sitemapHtml'])->name('sitemap.html');
Route::get('/sitemap/{file}.xml', [App\Http\Controllers\SeoController::class, 'sitemapType'])->where('file', '[a-z0-9-]+')->name('sitemap.type');
Route::get('/sitemap', [App\Http\Controllers\SeoController::class, 'sitemapHtml'])->name('sitemap.page');
Route::get('/robots.txt', [App\Http\Controllers\SeoController::class, 'robots'])->name('robots');
Route::get('/rss.xml', [App\Http\Controllers\SeoController::class, 'rss'])->name('rss');

// Push notifications (deal alerts)
Route::post('/push/subscribe', [App\Http\Controllers\PushController::class, 'subscribe'])->middleware('throttle:20,1')->name('push.subscribe');
Route::post('/push/unsubscribe', [App\Http\Controllers\PushController::class, 'unsubscribe'])->middleware('throttle:20,1')->name('push.unsubscribe');
Route::get('/push/click/{message}', [App\Http\Controllers\PushController::class, 'click'])->where('message', '[0-9]+')->name('push.click');

// Short referer links: /ref/{code}
Route::get('/ref/{code}', [App\Http\Controllers\RefererController::class, 'go'])
    ->where('code', '[A-Za-z0-9][A-Za-z0-9_\-\/.]*')->name('referer.go');

// Catch-all: resolves a roundup list, then a page. Must stay last.
Route::get('/{slug}', [PageController::class, 'resolve'])
    ->where('slug', '^(?!'.preg_quote(config('cms.filament_path', 'admin'), '/').'|livewire|storage|build|css|js).*$')
    ->name('page.show');
