<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogComment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogManagementController extends Controller
{
    /**
     * Blog posts list
     */
    public function posts()
    {
        $posts = BlogPost::with(['author', 'category'])
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => BlogPost::count(),
            'published' => BlogPost::where('status', 'published')->count(),
            'draft' => BlogPost::where('status', 'draft')->count(),
            'scheduled' => BlogPost::where('status', 'scheduled')->count(),
        ];

        return view('admin.blog.posts', compact('posts', 'stats'));
    }

    /**
     * Create post
     */
    public function createPost()
    {
        $categories = BlogCategory::orderBy('name')->get();
        return view('admin.blog.create-post', compact('categories'));
    }

    /**
     * Store post
     */
    public function storePost(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:5120',
            'category_id' => 'nullable|exists:blog_categories,id',
            'tags' => 'nullable|array',
            'status' => 'required|in:draft,published,scheduled',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
        ]);

        $imagePath = null;
        if ($request->hasFile('featured_image')) {
            $imagePath = $request->file('featured_image')->store('blog/images', 'public');
        }

        // Calculate reading time
        $wordCount = str_word_count(strip_tags($validated['content']));
        $readingTime = ceil($wordCount / 200); // Average reading speed

        $post = BlogPost::create([
            'author_id' => auth()->id(),
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'featured_image' => $imagePath,
            'category_id' => $validated['category_id'],
            'tags' => $validated['tags'] ?? [],
            'status' => $validated['status'],
            'reading_time' => $readingTime,
            'meta_title' => $validated['meta_title'] ?? $validated['title'],
            'meta_description' => $validated['meta_description'] ?? Str::limit($validated['excerpt'] ?? strip_tags($validated['content']), 160),
            'published_at' => $validated['status'] === 'published' ? now() : $validated['published_at'],
        ]);

        return redirect()->route('admin.blog.posts')
            ->with('success', 'Blog post created successfully!');
    }

    /**
     * Edit post
     */
    public function editPost(BlogPost $post)
    {
        $categories = BlogCategory::orderBy('name')->get();
        return view('admin.blog.edit-post', compact('post', 'categories'));
    }

    /**
     * Update post
     */
    public function updatePost(Request $request, BlogPost $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:5120',
            'category_id' => 'nullable|exists:blog_categories,id',
            'tags' => 'nullable|array',
            'status' => 'required|in:draft,published,scheduled',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
        ]);

        if ($request->hasFile('featured_image')) {
            if ($post->featured_image) {
                \Storage::disk('public')->delete($post->featured_image);
            }
            $validated['featured_image'] = $request->file('featured_image')->store('blog/images', 'public');
        }

        // Recalculate reading time
        $wordCount = str_word_count(strip_tags($validated['content']));
        $validated['reading_time'] = ceil($wordCount / 200);

        $post->update($validated);

        return back()->with('success', 'Blog post updated successfully!');
    }

    /**
     * Delete post
     */
    public function deletePost(BlogPost $post)
    {
        if ($post->featured_image) {
            \Storage::disk('public')->delete($post->featured_image);
        }

        $post->delete();

        return redirect()->route('admin.blog.posts')
            ->with('success', 'Blog post deleted successfully!');
    }

    /**
     * Categories
     */
    public function categories()
    {
        $categories = BlogCategory::withCount('posts')
            ->orderBy('order')
            ->get();

        return view('admin.blog.categories', compact('categories'));
    }

    /**
     * Store category
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'integer|min:0',
        ]);

        BlogCategory::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'order' => $validated['order'] ?? 0,
        ]);

        return back()->with('success', 'Category created successfully!');
    }

    /**
     * Comments moderation
     */
    public function comments()
    {
        $comments = BlogComment::with(['post', 'user'])
            ->latest()
            ->paginate(50);

        $stats = [
            'total' => BlogComment::count(),
            'approved' => BlogComment::where('is_approved', true)->count(),
            'pending' => BlogComment::where('is_approved', false)->count(),
        ];

        return view('admin.blog.comments', compact('comments', 'stats'));
    }

    /**
     * Approve comment
     */
    public function approveComment(BlogComment $comment)
    {
        $comment->update(['is_approved' => true]);
        return back()->with('success', 'Comment approved!');
    }

    /**
     * Delete comment
     */
    public function deleteComment(BlogComment $comment)
    {
        $comment->delete();
        return back()->with('success', 'Comment deleted!');
    }
}
