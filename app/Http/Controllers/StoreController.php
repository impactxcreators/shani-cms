<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Deal;
use App\Models\Store;

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

    public function coupons()
    {
        $coupons = Coupon::active()->with('store')->orderByDesc('is_verified')->orderBy('sort_order')->paginate(24);
        return view('coupons.index', compact('coupons'));
    }

    public function deals()
    {
        $deals = Deal::active()->with('store')->orderByDesc('is_featured')->orderBy('sort_order')->paginate(24);
        return view('deals.index', compact('deals'));
    }
}
