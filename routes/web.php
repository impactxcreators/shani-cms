<?php

use App\Http\Controllers\OutboundController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');

Route::get('/go/{type}/{id}', [OutboundController::class, 'go'])
    ->where('type', 'coupon|deal|store|product')->where('id', '[0-9]+')->name('go');

Route::get('/coupons', [StoreController::class, 'coupons'])->name('coupons.index');
Route::get('/deals', [StoreController::class, 'deals'])->name('deals.index');
Route::get('/best', [PageController::class, 'best'])->name('roundups.index');
Route::get('/store/{slug}', [StoreController::class, 'show'])->name('store.show');

// Catch-all: resolves a roundup list, then a page. Must stay last.
Route::get('/{slug}', [PageController::class, 'resolve'])
    ->where('slug', '^(?!'.preg_quote(config('cms.filament_path', 'admin'), '/').'|livewire|storage|build|css|js).*$')
    ->name('page.show');
