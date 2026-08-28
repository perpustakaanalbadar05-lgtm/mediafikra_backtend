<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookController extends Controller
{
    /**
     * Simpan gambar langsung ke public/img/{subdir}/ agar bisa diakses
     * Apache tanpa perlu symlink (solusi untuk cPanel shared hosting).
     */
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
        // Hapus dari public/img/
        if (str_starts_with($path, '/img/')) {
            $fullPath = public_path(ltrim($path, '/'));
            if (file_exists($fullPath)) @unlink($fullPath);
        }
        // Legacy: hapus dari storage jika masih ada
        if (str_starts_with($path, '/storage/')) {
            \Illuminate\Support\Facades\Storage::disk('public')
                ->delete(str_replace('/storage/', '', $path));
        }
    }

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
            'judul'          => 'required|string|max:255',
            'deskripsi'      => 'nullable|string',
            'sinopsis'       => 'nullable|string',
            'harga'          => 'required|integer|min:0',
            'stok'           => 'required|integer|min:0',
            'cover_image'    => 'nullable|image|max:2048',
            'kategori'       => 'nullable|string|max:100',
            'featured'       => 'boolean',
            'status_publish' => 'boolean',
        ]);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->saveImage($request->file('cover_image'), 'covers');
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
            'judul'          => 'sometimes|string|max:255',
            'deskripsi'      => 'nullable|string',
            'sinopsis'       => 'nullable|string',
            'harga'          => 'sometimes|integer|min:0',
            'stok'           => 'sometimes|integer|min:0',
            'cover_image'    => 'nullable|image|max:2048',
            'kategori'       => 'nullable|string|max:100',
            'featured'       => 'boolean',
            'status_publish' => 'boolean',
        ]);

        if ($request->hasFile('cover_image')) {
            $this->deleteImage($book->cover_image);
            $data['cover_image'] = $this->saveImage($request->file('cover_image'), 'covers');
        }

        if (isset($data['judul'])) {
            $data['slug'] = Str::slug($data['judul']) . '-' . $book->id;
        }

        $book->update($data);
        return response()->json($book);
    }

    public function destroy(Book $book)
    {
        $this->deleteImage($book->cover_image);
        $book->delete();
        return response()->json(['message' => 'Buku berhasil dihapus.']);
    }

    public function adminIndex()
    {
        return response()->json(Book::latest()->get());
    }
}
