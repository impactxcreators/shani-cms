<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\ProductList;

class PageController extends Controller
{
    public function home()
    {
        return view('home');
    }

    public function best()
    {
        $lists = ProductList::published()->withCount('items')->orderByDesc('updated_at')->paginate(24);
        return view('roundups.index', compact('lists'));
    }

    /**
     * Catch-all: resolve a published roundup first, then a page.
     */
    public function resolve(string $slug)
    {
        if ($list = ProductList::published()->where('slug', $slug)->first()) {
            $list->load(['items.product', 'category']);
            $related = ProductList::published()->whereIn('id', $list->related_list_ids ?? [])->get();
            return view('roundups.show', ['list' => $list, 'related' => $related]);
        }

        if ($page = Page::published()->where('slug', $slug)->first()) {
            return view('pages.show', ['page' => $page]);
        }

        abort(404);
    }
}
