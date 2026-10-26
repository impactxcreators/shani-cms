<?php

namespace App\Http\Controllers;

use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount(["stores"])
            ->orderByDesc("is_featured")
            ->orderBy("name")
            ->get();

        return view("categories.index", compact("categories"));
    }

    public function show(string $slug)
    {
        $category = Category::where("slug", $slug)->first();
        abort_if(! $category, 404);

        $stores = $category->stores()
            ->published()
            ->withCount([
                "coupons as active_coupons_count" => fn ($q) => $q->active(),
                "deals as active_deals_count" => fn ($q) => $q->active(),
            ])
            ->orderByDesc("is_featured")
            ->orderBy("name")
            ->paginate(48);

        return view("categories.show", compact("category", "stores"));
    }
}
