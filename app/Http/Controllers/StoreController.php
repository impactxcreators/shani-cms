<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Support\OfferFeed;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index()
    {
        $stores = Store::published()
            ->withCount([
                "coupons as active_coupons_count" => fn ($q) => $q->active(),
                "deals as active_deals_count" => fn ($q) => $q->active(),
            ])
            ->orderByDesc("is_featured")
            ->orderBy("name")
            ->paginate(48);

        return view("stores.index", compact("stores"));
    }

    public function show(string $slug)
    {
        $store = Store::published()->where('slug', $slug)->first();
        abort_if(! $store, 404);

        $coupons = $store->coupons()->active()->orderBy('sort_order')->get();
        $deals = $store->deals()->active()->orderBy('sort_order')->get();

        return view('stores.show', compact('store', 'coupons', 'deals'));
    }

    public function coupons(Request $request)
    {
        return $this->feed($request, 'coupons', 'coupons.index');
    }

    public function deals(Request $request)
    {
        return $this->feed($request, 'deals', 'deals.index');
    }

    private function feed(Request $request, string $type, string $view)
    {
        $scope = fn () => Store::published()->select('stores.id');
        $sort = OfferFeed::sort($request->query('sort'));
        $offers = OfferFeed::page($scope(), $type, $sort);
        $stats = OfferFeed::stats($scope(), $type);

        return view($view, compact('offers', 'stats', 'type', 'sort'));
    }
}
