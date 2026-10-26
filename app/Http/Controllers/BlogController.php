<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\BlogCategory;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    public function index()
    {
        $posts = Post::published()->with('author')->latest('published_at')->latest()->paginate(12);
        return view('blog.index', compact('posts'));
    }

    public function show(string $slug)
    {
        $post = Post::published()->with(['author', 'categories'])->where('slug', $slug)->first();
        abort_if(! $post, 404);

        $comments = $post->comments()->approved()->whereNull('parent_id')->with('replies')->latest()->get();
        return view('blog.show', compact('post', 'comments'));
    }

    public function category(string $slug)
    {
        $category = BlogCategory::where('slug', $slug)->firstOrFail();
        $posts = $category->posts()->published()->with('author')->latest('published_at')->paginate(12);
        return view('blog.index', ['posts' => $posts, 'heading' => $category->name]);
    }

    public function author(string $slug)
    {
        $author = Author::where('slug', $slug)->firstOrFail();
        $posts = $author->posts()->published()->latest('published_at')->paginate(12);
        return view('blog.index', ['posts' => $posts, 'heading' => 'Posts by '.$author->name]);
    }

    public function comment(Request $request, string $slug)
    {
        $post = Post::published()->where('slug', $slug)->firstOrFail();

        // Honeypot: real users never fill this hidden field.
        if ($request->filled('website')) {
            return back()->with('comment_status', 'Thanks!');
        }

        $data = Validator::make($request->all(), [
            'author_name' => 'required|string|max:120',
            'author_email' => 'nullable|email|max:190',
            'body' => 'required|string|max:5000',
        ])->validate();

        $post->comments()->create([
            'author_name' => $data['author_name'],
            'author_email' => $data['author_email'] ?? null,
            'body' => $data['body'],
            'status' => 'pending',
            'ip_hash' => hash('sha256', (string) $request->ip()),
            'user_agent' => substr((string) $request->userAgent(), 0, 512),
        ]);

        return back()->with('comment_status', 'Thanks! Your comment is awaiting moderation.');
    }
}
