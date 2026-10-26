<?php

namespace App\Http\Controllers;

use App\Models\Page;

class PageController extends Controller
{
    public function home()
    {
        return view('home');
    }

    public function show(string $slug)
    {
        $page = Page::published()->where('slug', $slug)->first();
        abort_if(! $page, 404);

        return view('pages.show', ['page' => $page]);
    }
}
