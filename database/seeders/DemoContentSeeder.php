<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Deal;
use App\Models\Store;
use Illuminate\Database\Seeder;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Remove placeholder/test entries
        Store::withTrashed()->where("slug", "yg")->get()->each->forceDelete();
        Category::where("slug", "v")->delete();

        // 2) Categories
        $cats = [];
        foreach ([
            ["Fashion", "fashion", "Clothing, shoes and accessories from top brands."],
            ["Electronics", "electronics", "Laptops, phones, audio and gadgets at the best prices."],
            ["Home & Kitchen", "home-kitchen", "Everything for a comfortable, well-equipped home."],
            ["Beauty", "beauty", "Skincare, makeup and self-care essentials."],
        ] as [$name, $slug, $desc]) {
            $cats[$slug] = Category::updateOrCreate(
                ["slug" => $slug],
                ["name" => $name, "description" => $desc, "is_featured" => $slug === "fashion"]
            );
        }

        // 3) Demo stores per category (fictional brands)
        $stores = [
            ["Urban Thread", "urban-thread", "fashion", "Urban Thread brings street-ready fashion with fresh drops every week. Find everyday staples and statement pieces without the designer price tag."],
            ["Velvet & Co", "velvet-and-co", "fashion", "Velvet & Co is a modern label focused on timeless, well-made wardrobe essentials and occasion wear."],
            ["TechNova", "technova", "electronics", "TechNova stocks the latest laptops, smartphones and smart-home gear, with price-match deals all year round."],
            ["GadgetPoint", "gadgetpoint", "electronics", "GadgetPoint is your one-stop shop for audio, wearables and accessories from the brands people actually want."],
            ["HomeHarbor", "homeharbor", "home-kitchen", "HomeHarbor makes it easy to kit out every room with cookware, storage and decor that lasts."],
            ["CasaCraft", "casacraft", "home-kitchen", "CasaCraft curates stylish, affordable home and kitchen pieces for comfortable modern living."],
            ["Glow Theory", "glow-theory", "beauty", "Glow Theory is a clean-beauty brand with dermatologist-backed skincare and cruelty-free makeup."],
            ["Pure Bloom", "pure-bloom", "beauty", "Pure Bloom offers natural self-care, haircare and fragrance made with responsibly sourced ingredients."],
        ];

        $couponSets = [
            [["15% off your first order", "WELCOME15", "percentage", 15, "New customers save 15% sitewide.", true],
             ["Free shipping over \$40", "FREESHIP", "free_shipping", 0, "No minimum fuss - free delivery on orders over \$40.", false]],
            [["\$20 off orders over \$100", "SAVE20", "fixed", 20, "Stack your basket and save a flat \$20.", true],
             ["Extra 10% off sale items", "EXTRA10", "percentage", 10, "Already reduced? Take another 10% off.", false]],
        ];

        foreach ($stores as $i => [$name, $slug, $catSlug, $desc]) {
            $store = Store::updateOrCreate(
                ["slug" => $slug],
                [
                    "name" => $name,
                    "description" => $desc,
                    "website_url" => "https://example.com",
                    "affiliate_url" => "https://example.com",
                    "is_active" => true,
                    "is_featured" => $i % 3 === 0,
                    "average_rating" => 4.2 + ($i % 4) * 0.2,
                    "status" => "published",
                    "published_at" => now(),
                ]
            );
            $store->categories()->syncWithoutDetaching([$cats[$catSlug]->id]);

            $set = $couponSets[$i % 2];
            foreach ($set as $sort => [$title, $code, $type, $val, $cdesc, $verified]) {
                Coupon::updateOrCreate(
                    ["store_id" => $store->id, "code" => $code],
                    [
                        "title" => $title, "description" => $cdesc,
                        "discount_type" => $type, "discount_value" => $val,
                        "destination_url" => "https://example.com",
                        "is_verified" => $verified, "is_exclusive" => $sort === 0,
                        "status" => "published", "sort_order" => $sort,
                        "expires_at" => now()->addDays(60),
                    ]
                );
            }

            Deal::updateOrCreate(
                ["store_id" => $store->id, "title" => "Best seller bundle"],
                [
                    "description" => "Our most popular pick, discounted for a limited time.",
                    "original_price" => 129.00, "sale_price" => 89.00, "currency" => "USD",
                    "destination_url" => "https://example.com",
                    "is_featured" => true, "status" => "published", "sort_order" => 0,
                    "expires_at" => now()->addDays(45),
                ]
            );
        }

        $this->command->info("Demo content seeded: ".count($stores)." stores across ".count($cats)." categories.");
    }
}
