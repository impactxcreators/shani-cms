<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase4Test extends TestCase
{
    use RefreshDatabase;

    private function path(): string { return config('cms.filament_path', 'admin'); }

    private function makePost(): Post
    {
        return Post::create(['title' => 'Hello World', 'slug' => 'hello-world', 'body' => '<p>Hi</p>',
            'status' => 'published', 'published_at' => now()]);
    }

    public function test_admin_editorial_screens_load(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        $this->actingAs(User::factory()->create());
        $p = $this->path();
        foreach (['posts', 'blog-categories', 'authors', 'handpicks', 'comments'] as $r) {
            $this->get("/{$p}/{$r}")->assertStatus(200);
        }
    }

    public function test_blog_index_and_post_render(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        $this->makePost();
        $this->get('/blog')->assertStatus(200)->assertSee('Hello World');
        $this->get('/blog/hello-world')->assertStatus(200)->assertSee('Hi', false);
    }

    public function test_draft_post_is_404(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        Post::create(['title' => 'Draft', 'slug' => 'draft-post', 'status' => 'draft']);
        $this->get('/blog/draft-post')->assertStatus(404);
    }

    public function test_only_approved_comments_show(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        $post = $this->makePost();
        $post->comments()->create(['author_name' => 'Alice', 'body' => 'Approved comment', 'status' => 'approved']);
        $post->comments()->create(['author_name' => 'Bob', 'body' => 'Pending comment', 'status' => 'pending']);

        $res = $this->get('/blog/hello-world')->assertStatus(200);
        $res->assertSee('Approved comment');
        $res->assertDontSee('Pending comment');
    }

    public function test_visitor_can_submit_comment_as_pending(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        $post = $this->makePost();
        $this->post('/blog/hello-world/comment', [
            'author_name' => 'Carol', 'author_email' => 'c@example.com', 'body' => 'Great post!',
        ])->assertRedirect();

        $this->assertDatabaseHas('comments', ['author_name' => 'Carol', 'status' => 'pending']);
    }

    public function test_honeypot_blocks_spam(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        $this->makePost();
        $this->post('/blog/hello-world/comment', [
            'author_name' => 'Spammer', 'body' => 'buy stuff', 'website' => 'http://spam.com',
        ]);
        $this->assertDatabaseMissing('comments', ['author_name' => 'Spammer']);
    }
}
