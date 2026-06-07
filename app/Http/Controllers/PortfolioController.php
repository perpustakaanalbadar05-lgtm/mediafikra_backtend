<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function index()
    {
        return response()->json(Portfolio::latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cover' => 'nullable|image|max:2048',
            'judul' => 'required|string|max:255',
            'penulis' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'tahun' => 'nullable|integer',
            'deskripsi' => 'nullable|string',
        ]);

        if ($request->hasFile('cover')) {
            $path = $request->file('cover')->store('portfolios', 'public');
            $data['cover'] = '/storage/' . $path;
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
            'cover' => 'nullable|image|max:2048',
            'judul' => 'sometimes|string|max:255',
            'penulis' => 'sometimes|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'tahun' => 'nullable|integer',
            'deskripsi' => 'nullable|string',
        ]);

        if ($request->hasFile('cover')) {
            if ($portfolio->cover && str_starts_with($portfolio->cover, '/storage/')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete(str_replace('/storage/', '', $portfolio->cover));
            }
            $path = $request->file('cover')->store('portfolios', 'public');
            $data['cover'] = '/storage/' . $path;
        }

        $portfolio->update($data);
        return response()->json($portfolio);
    }

    public function destroy(Portfolio $portfolio)
    {
        if ($portfolio->cover && str_starts_with($portfolio->cover, '/storage/')) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete(str_replace('/storage/', '', $portfolio->cover));
        }
        $portfolio->delete();
        return response()->json(['message' => 'Portfolio berhasil dihapus.']);
    }
}
