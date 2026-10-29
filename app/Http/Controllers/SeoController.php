<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\ProductList;
use App\Models\Setting;
use App\Models\Store;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;

class SeoController extends Controller
{
    /** Max URLs per child sitemap (Google allows 50,000). */
    private const PER_SITEMAP = 50000;

    /** Child sitemap types, in index order. Only types with real public pages. */
    private function sitemapTypes(): array
    {
        return ['main', 'pages', 'stores', 'store-categories', 'product-categories', 'roundups', 'blog', 'blog-categories'];
    }

    private function urlsForType(string $type): Collection
    {
        return match ($type) {
            'main' => collect([
                ['loc' => url('/'), 'lastmod' => now()],
                ['loc' => url('/store'), 'lastmod' => now()],
                ['loc' => url('/categories'), 'lastmod' => now()],
                ['loc' => url('/coupons'), 'lastmod' => now()],
                ['loc' => url('/deals'), 'lastmod' => now()],
                ['loc' => url('/best'), 'lastmod' => now()],
                ['loc' => url('/blog'), 'lastmod' => now()],
            ]),
            'pages' => Page::published()->get(['slug', 'updated_at'])
                ->map(fn ($p) => ['loc' => url('/'.$p->slug), 'lastmod' => $p->updated_at]),
            'stores' => Store::published()->get(['slug', 'updated_at'])
                ->map(fn ($s) => ['loc' => url('/store/'.$s->slug), 'lastmod' => $s->updated_at]),
            'store-categories' => Category::all(['slug', 'updated_at'])
                ->map(fn ($c) => ['loc' => url('/category/'.$c->slug), 'lastmod' => $c->updated_at]),
            'product-categories' => \App\Models\ProductCategory::query()
                ->where(function ($q) {
                    $q->whereHas('products', fn ($p) => $p->where('is_available', true))
                        ->orWhereHas('productLists', fn ($l) => $l->published());
                })
                ->get(['slug', 'updated_at'])
                ->map(fn ($c) => ['loc' => url('/product-category/'.$c->slug), 'lastmod' => $c->updated_at]),
            'roundups' => ProductList::published()->get(['slug', 'updated_at'])
                ->map(fn ($l) => ['loc' => url('/'.$l->slug), 'lastmod' => $l->updated_at]),
            'blog' => Post::published()->get(['slug', 'updated_at'])
                ->map(fn ($p) => ['loc' => url('/blog/'.$p->slug), 'lastmod' => $p->updated_at]),
            'blog-categories' => BlogCategory::all(['slug', 'updated_at'])
                ->map(fn ($c) => ['loc' => url('/blog/category/'.$c->slug), 'lastmod' => $c->updated_at]),
            default => collect(),
        };
    }

    /** The sitemap index at /sitemap.xml */
    public function sitemap()
    {
        $entries = [];
        foreach ($this->sitemapTypes() as $type) {
            $urls = $this->urlsForType($type);
            $count = $urls->count();
            if ($count === 0 && $type !== 'main') {
                continue;
            }
            $lastmod = $urls->max('lastmod') ?: now();
            $pages = max(1, (int) ceil($count / self::PER_SITEMAP));
            for ($p = 1; $p <= $pages; $p++) {
                $loc = $pages > 1
                    ? url('/sitemap/'.$type.'-'.$p.'.xml')
                    : url('/sitemap/'.$type.'.xml');
                $entries[] = ['loc' => $loc, 'lastmod' => $lastmod];
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($entries as $e) {
            $xml .= '  <sitemap><loc>'.e($e['loc']).'</loc>'
                .'<lastmod>'.optional($e['lastmod'])->toAtomString().'</lastmod></sitemap>'."\n";
        }
        $xml .= '</sitemapindex>';

        return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
    }

    /** A child sitemap, e.g. /sitemap/stores.xml or /sitemap/stores-2.xml */
    public function sitemapType(string $file)
    {
        if (preg_match('/^(.*)-(\d+)$/', $file, $m)) {
            $type = $m[1];
            $page = (int) $m[2];
        } else {
            $type = $file;
            $page = 1;
        }

        if (! in_array($type, $this->sitemapTypes(), true)) {
            abort(404);
        }

        $chunk = $this->urlsForType($type)->forPage($page, self::PER_SITEMAP);
        if ($chunk->isEmpty()) {
            abort(404);
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($chunk as $u) {
            $xml .= '  <url><loc>'.e($u['loc']).'</loc>';
            if (! empty($u['lastmod'])) {
                $xml .= '<lastmod>'.optional($u['lastmod'])->toAtomString().'</lastmod>';
            }
            $xml .= '</url>'."\n";
        }
        $xml .= '</urlset>';

        return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
    }

    /** Human-friendly browse page at /sitemap (product categories excluded — they are internal labels). */
    public function sitemapHtml()
    {
        $storeCats = Category::with(['stores' => fn ($q) => $q->published()])->get();
        $storeTree = $this->categoryTree(
            $storeCats,
            fn ($c) => route('category.show', $c->slug),
            fn ($c) => $c->stores->take(100)->map(fn ($s) => ['label' => $s->name, 'url' => route('store.show', $s->slug)])->values()->all()
        );

        $roundups = ProductList::published()->latest('updated_at')->limit(300)->get(['slug', 'title', 'h1'])
            ->map(fn ($r) => ['label' => ($r->h1 ?: $r->title), 'url' => url('/'.$r->slug)])->values()->all();

        $blogTree = BlogCategory::with(['posts' => fn ($q) => $q->published()])->get()->map(fn ($c) => [
            'name' => $c->name,
            'url' => route('blog.category', $c->slug),
            'pages' => $c->posts->map(fn ($p) => ['label' => $p->title, 'url' => route('blog.show', $p->slug)])->values()->all(),
            'children' => [],
        ])->values()->all();

        $pages = Page::published()->get(['slug', 'title'])
            ->map(fn ($p) => ['label' => $p->title, 'url' => url('/'.$p->slug)])->values()->all();

        return view('seo.sitemap', compact('storeTree', 'roundups', 'blogTree', 'pages'));
    }

    private function categoryTree($all, callable $urlFn, callable $pagesFn, $parentId = null): array
    {
        return $all->where('parent_id', $parentId)->map(fn ($c) => [
            'name' => $c->name,
            'url' => $urlFn($c),
            'pages' => $pagesFn($c),
            'children' => $this->categoryTree($all, $urlFn, $pagesFn, $c->id),
        ])->values()->all();
    }

    public function robots()
    {
        $maintenance = Setting::get('maintenance_mode');
        $lines = $maintenance
            ? ["User-agent: *", "Disallow: /"]
            : ["User-agent: *", "Allow: /", "Disallow: /".config('cms.filament_path', 'admin'), "Disallow: /ref/", "Sitemap: ".url('/sitemap.xml')];

        return Response::make(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain']);
    }

    public function rss()
    {
        $posts = Post::published()->latest('published_at')->limit(30)->get();
        $site = Setting::get('site_name', 'Shani CMS');
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<rss version="2.0"><channel>'."\n";
        $xml .= '<title>'.e($site).'</title><link>'.e(url('/')).'</link>';
        $xml .= '<description>'.e(Setting::get('site_description', '')).'</description>'."\n";
        foreach ($posts as $p) {
            $xml .= '<item><title>'.e($p->title).'</title>'
                .'<link>'.e(url('/blog/'.$p->slug)).'</link>'
                .'<guid>'.e(url('/blog/'.$p->slug)).'</guid>'
                .'<pubDate>'.optional($p->published_at)->toRssString().'</pubDate>'
                .'<description>'.e($p->excerpt ?? '').'</description></item>'."\n";
        }
        $xml .= '</channel></rss>';

        return Response::make($xml, 200, ['Content-Type' => 'application/rss+xml']);
    }
}
