<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');

// Catch-all for CMS-managed pages. Must stay last so named routes win first.
// Excludes the admin panel path and framework asset paths.
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '^(?!'.preg_quote(config('cms.filament_path', 'admin'), '/').'|livewire|storage|build|css|js).*$')
    ->name('page.show');
