<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Deal;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductList;
use App\Models\Store;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query("q", ""));
        $groups = [];
        $total = 0;

        if ($q !== "" && mb_strlen($q) >= 2) {
            $like = "%" . str_replace(["%", "_"], ["\\%", "\\_"], $q) . "%";

            $stores = Store::published()
                ->where(fn ($w) => $w->where("name", "like", $like)->orWhere("description", "like", $like))
                ->limit(10)->get()
                ->map(fn ($s) => ["title" => $s->name, "snippet" => str($s->description)->stripTags()->limit(120), "url" => route("store.show", $s->slug)]);

            $roundups = ProductList::published()
                ->where(fn ($w) => $w->where("title", "like", $like)->orWhere("h1", "like", $like)->orWhere("subtitle", "like", $like)->orWhere("intro", "like", $like))
                ->limit(10)->get()
                ->map(fn ($r) => ["title" => $r->h1 ?: $r->title, "snippet" => str($r->subtitle ?: $r->intro)->stripTags()->limit(120), "url" => url("/" . $r->slug)]);

            $posts = Post::published()
                ->where(fn ($w) => $w->where("title", "like", $like)->orWhere("excerpt", "like", $like)->orWhere("body", "like", $like))
                ->limit(10)->get()
                ->map(fn ($p) => ["title" => $p->title, "snippet" => str($p->excerpt ?: $p->body)->stripTags()->limit(120), "url" => route("blog.show", $p->slug)]);

            $coupons = Coupon::active()->with("store")
                ->where(fn ($w) => $w->where("title", "like", $like)->orWhere("code", "like", $like)->orWhere("description", "like", $like))
                ->limit(10)->get()
                ->filter(fn ($c) => $c->store)
                ->map(fn ($c) => ["title" => $c->title, "snippet" => ($c->store?->name ? $c->store->name . " coupon" : "Coupon"), "url" => route("store.show", $c->store->slug)]);

            $deals = Deal::where("status", "published")->with("store")
                ->where(fn ($w) => $w->where("title", "like", $like)->orWhere("description", "like", $like))
                ->limit(10)->get()
                ->filter(fn ($d) => $d->store)
                ->map(fn ($d) => ["title" => $d->title, "snippet" => str($d->description)->stripTags()->limit(120), "url" => route("store.show", $d->store->slug)]);

            $products = Product::where("is_available", true)
                ->where(fn ($w) => $w->where("title", "like", $like)->orWhere("brand", "like", $like)->orWhere("description", "like", $like))
                ->limit(10)->get()
                ->map(fn ($p) => ["title" => $p->title, "snippet" => str($p->description)->stripTags()->limit(120), "url" => route("go", ["type" => "product", "id" => $p->id])]);

            $pages = Page::published()
                ->where(fn ($w) => $w->where("title", "like", $like)->orWhere("body", "like", $like))
                ->limit(10)->get()
                ->map(fn ($p) => ["title" => $p->title, "snippet" => str($p->body)->stripTags()->limit(120), "url" => url("/" . $p->slug)]);

            foreach ([
                "Stores" => $stores, "Best picks" => $roundups, "Articles" => $posts,
                "Coupons" => $coupons, "Deals" => $deals, "Products" => $products, "Pages" => $pages,
            ] as $label => $items) {
                if ($items->isNotEmpty()) {
                    $groups[$label] = $items;
                    $total += $items->count();
                }
            }
        }

        return view("search.results", compact("q", "groups", "total"));
    }
}
