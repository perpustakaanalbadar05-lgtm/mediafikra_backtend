<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    private function saveImage($file, string $subdir): string
    {
        $dir = public_path('img/' . $subdir);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $file->move($dir, $filename);
        return '/img/' . $subdir . '/' . $filename;
    }

    private function deleteImage(?string $path): void
    {
        if (!$path) return;
        if (str_starts_with($path, '/img/')) {
            $fullPath = public_path(ltrim($path, '/'));
            if (file_exists($fullPath)) @unlink($fullPath);
        }
        if (str_starts_with($path, '/storage/')) {
            \Illuminate\Support\Facades\Storage::disk('public')
                ->delete(str_replace('/storage/', '', $path));
        }
    }

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
            'title'     => 'required|string|max:255',
            'content'   => 'required|string',
            'status'    => 'required|string|in:published,draft',
            'thumbnail' => 'nullable|image|max:2048',
        ]);
        $validated['slug'] = Str::slug($validated['title']) . '-' . time();

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $this->saveImage($request->file('thumbnail'), 'articles');
        }

        $article = Article::create($validated);
        return response()->json($article, 201);
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title'     => 'sometimes|string|max:255',
            'content'   => 'sometimes|string',
            'status'    => 'sometimes|string|in:published,draft',
            'thumbnail' => 'nullable|image|max:2048',
        ]);

        if (isset($validated['title'])) {
            $validated['slug'] = Str::slug($validated['title']) . '-' . $article->id;
        }

        if ($request->hasFile('thumbnail')) {
            $this->deleteImage($article->thumbnail);
            $validated['thumbnail'] = $this->saveImage($request->file('thumbnail'), 'articles');
        }

        $article->update($validated);
        return response()->json($article);
    }

    public function destroy(Article $article)
    {
        $this->deleteImage($article->thumbnail);
        $article->delete();
        return response()->json(['message' => 'Article deleted']);
    }
}
