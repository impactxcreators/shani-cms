<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\Category;
use App\Models\Page;
use App\Models\ProductCategory;
use App\Models\Post;
use App\Models\ProductList;
use App\Models\Setting;
use App\Models\Store;
use Illuminate\Support\Facades\Response;

class SeoController extends Controller
{
    private function urls(): array
    {
        $urls = [['loc' => url('/'), 'lastmod' => now()]];

        foreach (Page::published()->get(['slug', 'updated_at']) as $p) {
            $urls[] = ['loc' => url('/'.$p->slug), 'lastmod' => $p->updated_at];
        }
        foreach (ProductList::published()->get(['slug', 'updated_at']) as $l) {
            $urls[] = ['loc' => url('/'.$l->slug), 'lastmod' => $l->updated_at];
        }
        $urls[] = ['loc' => url('/store'), 'lastmod' => now()];
        $urls[] = ['loc' => url('/categories'), 'lastmod' => now()];
        foreach (\App\Models\Category::all(['slug', 'updated_at']) as $c) {
            $urls[] = ['loc' => url('/category/'.$c->slug), 'lastmod' => $c->updated_at];
        }
        foreach (Store::published()->get(['slug', 'updated_at']) as $s) {
            $urls[] = ['loc' => url('/store/'.$s->slug), 'lastmod' => $s->updated_at];
        }
        foreach (Post::published()->get(['slug', 'updated_at']) as $post) {
            $urls[] = ['loc' => url('/blog/'.$post->slug), 'lastmod' => $post->updated_at];
        }

        return $urls;
    }

    public function sitemap()
    {
        $urls = $this->urls();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $u) {
            $xml .= '  <url><loc>'.e($u['loc']).'</loc>'
                .'<lastmod>'.optional($u['lastmod'])->toAtomString().'</lastmod></url>'."\n";
        }
        $xml .= '</urlset>';

        return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function sitemapHtml()
    {
        $storeCats = Category::with(['stores' => fn ($q) => $q->published()])->get();
        $storeTree = $this->categoryTree(
            $storeCats,
            fn ($c) => route('category.show', $c->slug),
            fn ($c) => $c->stores->map(fn ($s) => ['label' => $s->name, 'url' => route('store.show', $s->slug)])->values()->all()
        );

        $roundupsByCat = ProductList::published()->get(['slug', 'title', 'h1', 'product_category_id'])->groupBy('product_category_id');
        $productTree = $this->categoryTree(
            ProductCategory::all(),
            fn ($c) => null,
            fn ($c) => collect($roundupsByCat->get($c->id, collect()))
                ->map(fn ($r) => ['label' => ($r->h1 ?: $r->title), 'url' => url('/'.$r->slug)])->values()->all()
        );

        $blogTree = BlogCategory::with(['posts' => fn ($q) => $q->published()])->get()->map(fn ($c) => [
            'name' => $c->name,
            'url' => route('blog.category', $c->slug),
            'pages' => $c->posts->map(fn ($p) => ['label' => $p->title, 'url' => route('blog.show', $p->slug)])->values()->all(),
            'children' => [],
        ])->values()->all();

        $pages = Page::published()->get(['slug', 'title'])
            ->map(fn ($p) => ['label' => $p->title, 'url' => url('/'.$p->slug)])->values()->all();

        return view('seo.sitemap', compact('storeTree', 'productTree', 'blogTree', 'pages'));
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
            : ["User-agent: *", "Allow: /", "Disallow: /".config('cms.filament_path', 'admin'), "Sitemap: ".url('/sitemap.xml')];

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
