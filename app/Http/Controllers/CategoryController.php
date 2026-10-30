<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Store;
use App\Support\OfferFeed;
use Illuminate\Http\Request;

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

    public function show(Request $request, string $slug)
    {
        $category = Category::where("slug", $slug)->first();
        abort_if(! $category, 404);

        // Offers from this category and all of its sub-categories.
        $ids = $this->withDescendants($category->id);
        $scope = fn () => Store::published()
            ->whereHas("categories", fn ($query) => $query->whereIn("categories.id", $ids))
            ->select("stores.id");

        $type = OfferFeed::type($request->query("type"));
        $sort = OfferFeed::sort($request->query("sort"));
        $offers = OfferFeed::page($scope(), $type, $sort);
        $stats = OfferFeed::stats($scope());

        $stores = Store::published()->whereIn("id", $scope())
            ->orderByDesc("is_featured")->orderBy("name")
            ->limit(40)->get(["id", "name", "slug", "logo"]);
        $children = $category->children()->orderBy("sort_order")->orderBy("name")->get(["id", "name", "slug"]);

        return view("categories.show", compact("category", "offers", "stats", "stores", "children", "type", "sort"));
    }

    private function withDescendants(int $id): array
    {
        $ids = [$id];
        $level = [$id];
        while ($level) {
            $level = Category::whereIn("parent_id", $level)->whereNotIn("id", $ids)->pluck("id")->all();
            $ids = array_merge($ids, $level);
        }

        return $ids;
    }
}
