<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminPostController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of the posts.
     */
    public function index()
    {
        $posts = Post::with('user')
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);

        return view('admin.posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new post.
     */
    public function create()
    {
        return view('admin.posts.create');
    }

    /**
     * Store a newly created post in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:posts,slug',
            'image' => 'nullable|image|max:2048',
            'category' => 'nullable|string|max:255',
            'tags' => 'nullable|string|max:255',
            'status' => 'required|string|in:draft,published,archived',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'body' => 'required|string',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('post_images', 'public');
            $validated['image'] = $imagePath;
        }

        // Set user_id to the authenticated admin
        $validated['user_id'] = auth()->id();

        // Create the post
        $post = Post::create($validated);

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post created successfully!');
    }

    /**
     * Display the specified post.
     */
    public function show(Post $post)
    {
        return view('admin.posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(Post $post)
    {
        return view('admin.posts.edit', compact('post'));
    }

    /**
     * Update the specified post in storage.
     */
    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:posts,slug,' . $post->id,
            'image' => 'nullable|image|max:2048',
            'category' => 'nullable|string|max:255',
            'tags' => 'nullable|string|max:255',
            'status' => 'required|string|in:draft,published,archived',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'body' => 'required|string',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }

            $imagePath = $request->file('image')->store('post_images', 'public');
            $validated['image'] = $imagePath;
        }

        // Update the post
        $post->update($validated);

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post updated successfully!');
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy(Post $post)
    {
        // Delete the post image if exists
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post deleted successfully!');
    }

    /**
     * Toggle post status between published and draft.
     */
    public function toggleStatus(Post $post)
    {
        $newStatus = $post->status === 'published' ? 'draft' : 'published';
        $post->update(['status' => $newStatus]);

        return redirect()
            ->back()
            ->with('success', "Post status changed to {$newStatus}!");
    }

    /**
     * Bulk action on multiple posts.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,publish,draft,archive',
            'posts' => 'required|array',
            'posts.*' => 'exists:posts,id'
        ]);

        $action = $request->input('action');
        $postIds = $request->input('posts');

        switch ($action) {
            case 'delete':
                // Get posts with images to delete them
                $postsWithImages = Post::whereIn('id', $postIds)
                    ->whereNotNull('image')
                    ->pluck('image');

                // Delete images
                foreach ($postsWithImages as $image) {
                    Storage::disk('public')->delete($image);
                }

                // Delete posts
                Post::whereIn('id', $postIds)->delete();
                $message = 'Selected posts have been deleted';
                break;

            case 'publish':
                Post::whereIn('id', $postIds)->update(['status' => 'published']);
                $message = 'Selected posts have been published';
                break;

            case 'draft':
                Post::whereIn('id', $postIds)->update(['status' => 'draft']);
                $message = 'Selected posts have been set to draft';
                break;

            case 'archive':
                Post::whereIn('id', $postIds)->update(['status' => 'archived']);
                $message = 'Selected posts have been archived';
                break;
        }

        return redirect()
            ->route('admin.posts.index')
            ->with('success', $message);
    }

    /**
     * Generate a unique slug from the title.
     */
    public function generateSlug(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $slug = Str::slug($request->title);
        $originalSlug = $slug;
        $count = 1;

        // Make sure the slug is unique
        while (Post::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        return response()->json(['slug' => $slug]);
    }
}
