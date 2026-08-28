<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PromoController extends Controller
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

    public function index(Request $request)
    {
        $query = Promo::where('status_publish', true);

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        return response()->json($query->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul'          => 'required|string|max:255',
            'isi'            => 'required|string',
            'thumbnail'      => 'nullable|image|max:2048',
            'status_publish' => 'boolean',
            'type'           => 'required|in:promo,berita',
        ]);

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $this->saveImage($request->file('thumbnail'), 'promos');
        }

        $data['slug'] = Str::slug($request->judul) . '-' . uniqid();
        return response()->json(Promo::create($data), 201);
    }

    public function show(Promo $promo)
    {
        return response()->json($promo);
    }

    public function update(Request $request, Promo $promo)
    {
        $data = $request->validate([
            'judul'          => 'sometimes|string|max:255',
            'isi'            => 'sometimes|string',
            'thumbnail'      => 'nullable|image|max:2048',
            'status_publish' => 'boolean',
            'type'           => 'sometimes|in:promo,berita',
        ]);

        if ($request->hasFile('thumbnail')) {
            $this->deleteImage($promo->thumbnail);
            $data['thumbnail'] = $this->saveImage($request->file('thumbnail'), 'promos');
        }

        if (isset($data['judul'])) {
            $data['slug'] = Str::slug($data['judul']) . '-' . $promo->id;
        }

        $promo->update($data);
        return response()->json($promo);
    }

    public function destroy(Promo $promo)
    {
        $this->deleteImage($promo->thumbnail);
        $promo->delete();
        return response()->json(['message' => 'Promo/berita berhasil dihapus.']);
    }

    public function adminIndex()
    {
        return response()->json(Promo::latest()->get());
    }
}
