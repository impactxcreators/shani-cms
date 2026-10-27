<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductList;

class ProductCategoryController extends Controller
{
    public function show(string $slug)
    {
        $category = ProductCategory::where('slug', $slug)->first();
        abort_if(! $category, 404);

        $roundups = ProductList::published()
            ->where('product_category_id', $category->id)
            ->latest('updated_at')
            ->get();

        $products = Product::where('product_category_id', $category->id)
            ->where('is_available', true)
            ->orderByDesc('rating')
            ->paginate(48);

        return view('product-categories.show', compact('category', 'roundups', 'products'));
    }
}
