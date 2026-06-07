<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::query();

        if ($request->has('kategori') && $request->kategori) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->has('featured')) {
            $query->where('featured', true);
        }

        if ($request->has('search') && $request->search) {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        return response()->json($query->where('status_publish', true)->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'sinopsis' => 'nullable|string',
            'harga' => 'required|integer|min:0',
            'stok' => 'required|integer|min:0',
            'cover_image' => 'nullable|image|max:2048', // 2MB max
            'kategori' => 'nullable|string|max:100',
            'featured' => 'boolean',
            'status_publish' => 'boolean',
        ]);

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('covers', 'public');
            $data['cover_image'] = '/storage/' . $path;
        }

        $data['slug'] = Str::slug($request->judul) . '-' . uniqid();
        $book = Book::create($data);

        return response()->json($book, 201);
    }

    public function show(Book $book)
    {
        return response()->json($book);
    }

    public function showBySlug($slug)
    {
        $book = Book::where('slug', $slug)->firstOrFail();
        return response()->json($book);
    }

    public function update(Request $request, Book $book)
    {
        $data = $request->validate([
            'judul' => 'sometimes|string|max:255',
            'deskripsi' => 'nullable|string',
            'sinopsis' => 'nullable|string',
            'harga' => 'sometimes|integer|min:0',
            'stok' => 'sometimes|integer|min:0',
            'cover_image' => 'nullable|image|max:2048',
            'kategori' => 'nullable|string|max:100',
            'featured' => 'boolean',
            'status_publish' => 'boolean',
        ]);

        if ($request->hasFile('cover_image')) {
            // Delete old if exists
            if ($book->cover_image && str_starts_with($book->cover_image, '/storage/')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete(str_replace('/storage/', '', $book->cover_image));
            }
            $path = $request->file('cover_image')->store('covers', 'public');
            $data['cover_image'] = '/storage/' . $path;
        }

        if (isset($data['judul'])) {
            $data['slug'] = Str::slug($data['judul']) . '-' . $book->id;
        }

        $book->update($data);
        return response()->json($book);
    }

    public function destroy(Book $book)
    {
        if ($book->cover_image && str_starts_with($book->cover_image, '/storage/')) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete(str_replace('/storage/', '', $book->cover_image));
        }
        $book->delete();
        return response()->json(['message' => 'Buku berhasil dihapus.']);
    }

    // Admin: semua buku termasuk yang tidak publish
    public function adminIndex()
    {
        return response()->json(Book::latest()->get());
    }
}
