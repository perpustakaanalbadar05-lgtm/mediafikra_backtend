<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PortfolioController extends Controller
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
        return response()->json(Portfolio::latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cover'     => 'nullable|image|max:2048',
            'judul'     => 'required|string|max:255',
            'penulis'   => 'required|string|max:255',
            'kategori'  => 'nullable|string|max:100',
            'tahun'     => 'nullable|integer',
            'deskripsi' => 'nullable|string',
        ]);

        if ($request->hasFile('cover')) {
            $data['cover'] = $this->saveImage($request->file('cover'), 'portfolios');
        }

        return response()->json(Portfolio::create($data), 201);
    }

    public function show(Portfolio $portfolio)
    {
        return response()->json($portfolio);
    }

    public function update(Request $request, Portfolio $portfolio)
    {
        $data = $request->validate([
            'cover'     => 'nullable|image|max:2048',
            'judul'     => 'sometimes|string|max:255',
            'penulis'   => 'sometimes|string|max:255',
            'kategori'  => 'nullable|string|max:100',
            'tahun'     => 'nullable|integer',
            'deskripsi' => 'nullable|string',
        ]);

        if ($request->hasFile('cover')) {
            $this->deleteImage($portfolio->cover);
            $data['cover'] = $this->saveImage($request->file('cover'), 'portfolios');
        }

        $portfolio->update($data);
        return response()->json($portfolio);
    }

    public function destroy(Portfolio $portfolio)
    {
        $this->deleteImage($portfolio->cover);
        $portfolio->delete();
        return response()->json(['message' => 'Portfolio berhasil dihapus.']);
    }
}
