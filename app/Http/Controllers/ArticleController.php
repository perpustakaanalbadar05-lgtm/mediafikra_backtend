<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index()
    {
        return response()->json(Article::latest()->get());
    }

    public function show($slug)
    {
        $article = Article::where('slug', $slug)->firstOrFail();
        return response()->json($article);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|string|in:published,draft',
            'thumbnail' => 'nullable|image|max:2048',
        ]);
        $validated['slug'] = Str::slug($validated['title']) . '-' . time();
        
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('articles', 'public');
            $validated['thumbnail'] = '/storage/' . $path;
        }
        
        $article = Article::create($validated);
        return response()->json($article, 201);
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'status' => 'sometimes|string|in:published,draft',
            'thumbnail' => 'nullable|image|max:2048',
        ]);

        if (isset($validated['title'])) {
            $validated['slug'] = Str::slug($validated['title']) . '-' . $article->id;
        }

        if ($request->hasFile('thumbnail')) {
            if ($article->thumbnail && str_starts_with($article->thumbnail, '/storage/')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete(str_replace('/storage/', '', $article->thumbnail));
            }
            $path = $request->file('thumbnail')->store('articles', 'public');
            $validated['thumbnail'] = '/storage/' . $path;
        }

        $article->update($validated);
        return response()->json($article);
    }

    public function destroy(Article $article)
    {
        if ($article->thumbnail && str_starts_with($article->thumbnail, '/storage/')) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete(str_replace('/storage/', '', $article->thumbnail));
        }
        $article->delete();
        return response()->json(['message' => 'Article deleted']);
    }
}
