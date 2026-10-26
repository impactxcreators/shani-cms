<?php

use App\Http\Controllers\OutboundController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');

// Affiliate outbound redirect with click tracking.
Route::get('/go/{type}/{id}', [OutboundController::class, 'go'])
    ->where('type', 'coupon|deal|store')->where('id', '[0-9]+')->name('go');

// Commerce listings & store pages.
Route::get('/coupons', [StoreController::class, 'coupons'])->name('coupons.index');
Route::get('/deals', [StoreController::class, 'deals'])->name('deals.index');
Route::get('/store/{slug}', [StoreController::class, 'show'])->name('store.show');

// Catch-all for CMS-managed pages. Must stay last.
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '^(?!'.preg_quote(config('cms.filament_path', 'admin'), '/').'|go|store|coupons|deals|livewire|storage|build|css|js).*$')
    ->name('page.show');
