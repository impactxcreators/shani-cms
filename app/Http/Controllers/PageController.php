<?php

namespace App\Http\Controllers;

use App\Models\HomeBanner;
use App\Models\HomeBlock;
use App\Models\Page;
use App\Models\ProductList;

class PageController extends Controller
{
    public function home()
    {
        $blocks = HomeBlock::active()->orderBy('sort_order')->get();
        $banners = HomeBanner::active()->orderBy('sort_order')->get();

        return view('home', compact('blocks', 'banners'));
    }

    public function best()
    {
        $lists = ProductList::published()->withCount('items')->orderByDesc('updated_at')->paginate(24);
        return view('roundups.index', compact('lists'));
    }

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
