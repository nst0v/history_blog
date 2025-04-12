<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Log;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PostController extends Controller
{
    public function index()
    {
        return Post::with('user')->latest()->get();  // Получаем посты с автором
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:posts,slug',
            'image' => 'nullable|image',
            'category' => 'nullable|string',
            'tags' => 'nullable|string',
            'status' => 'nullable|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'body' => 'required|string',
        ]);
        Log::info('Post store data:', $request->all());
        // Обработка изображения, если оно есть
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('post_images', 'public');
            $validated['image'] = $imagePath;
        }

        $post = Post::create([
            'user_id' => 1,// auth()->id(),  // Понимание, кто создал пост
            ...$validated,
        ]);

        return response()->json($post, 201);
    }

    public function show(Post $post)
    {
        return response()->json($post->load('user'));  // Загружаем пост с автором
    }

    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|unique:posts,slug,' . $post->id,
            'image' => 'nullable|image',
            'category' => 'nullable|string',
            'tags' => 'nullable|string',
            'status' => 'nullable|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'body' => 'sometimes|string',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('post_images', 'public');
            $validated['image'] = $imagePath;
        }

        $post->update($validated);
        return response()->json($post);
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return response()->noContent();
    }
}
