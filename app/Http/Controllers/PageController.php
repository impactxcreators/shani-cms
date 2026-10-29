<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\HomeBanner;
use App\Models\HomeBlock;
use App\Models\Page;
use App\Models\Post;
use App\Models\ProductList;
use App\Models\Setting;
use App\Models\Store;
use Illuminate\Support\Facades\Cache;

class PageController extends Controller
{
    public function home()
    {
        $blocks = HomeBlock::active()->orderBy("sort_order")->get();
        $banners = HomeBanner::active()->orderBy("sort_order")->get();
        [$exploreLinks, $exploreTotal, $exploreMinutes] = $this->exploreLinks();

        return view("home", compact("blocks", "banners", "exploreLinks", "exploreTotal", "exploreMinutes"));
    }

    /**
     * A rotating window of site links for the home page. The full pool is cached;
     * the visible window advances every N minutes so, over time, every page gets
     * home-page exposure without ever listing thousands of links at once.
     */
    private function exploreLinks(): array
    {
        $count = (int) Setting::get("home_links_count", 100);
        $count = $count > 0 ? $count : 100;
        $minutes = (int) Setting::get("home_links_interval_minutes", 60);
        $minutes = $minutes > 0 ? $minutes : 60;

        $pool = Cache::remember("home_link_pool", now()->addMinutes(min($minutes, 30)), function () {
            $links = [];
            foreach (Store::published()->orderBy("name")->get(["slug", "name"]) as $s) {
                $links[] = ["label" => $s->name, "url" => url("/store/".$s->slug)];
            }
            foreach (ProductList::published()->orderByDesc("updated_at")->get(["slug", "title", "h1"]) as $r) {
                $links[] = ["label" => ($r->h1 ?: $r->title), "url" => url("/".$r->slug)];
            }
            foreach (Post::published()->latest("published_at")->get(["slug", "title"]) as $p) {
                $links[] = ["label" => $p->title, "url" => url("/blog/".$p->slug)];
            }
            foreach (Category::orderBy("name")->get(["slug", "name"]) as $c) {
                $links[] = ["label" => $c->name, "url" => url("/category/".$c->slug)];
            }
            foreach (Page::published()->get(["slug", "title"]) as $p) {
                $links[] = ["label" => $p->title, "url" => url("/".$p->slug)];
            }
            return $links;
        });

        $total = count($pool);
        if ($total === 0) {
            return [[], 0, $minutes];
        }

        $bucket = intdiv(time(), $minutes * 60);
        $offset = ($bucket * $count) % $total;
        $window = array_slice($pool, $offset, $count);
        if (count($window) < $count && $total > $count) {
            $window = array_merge($window, array_slice($pool, 0, $count - count($window)));
        }

        return [$window, $total, $minutes];
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
