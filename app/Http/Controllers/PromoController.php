<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PromoController extends Controller
{
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
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'thumbnail' => 'nullable|string',
            'status_publish' => 'boolean',
            'type' => 'required|in:promo,berita',
        ]);

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
            'judul' => 'sometimes|string|max:255',
            'isi' => 'sometimes|string',
            'thumbnail' => 'nullable|string',
            'status_publish' => 'boolean',
            'type' => 'sometimes|in:promo,berita',
        ]);

        if (isset($data['judul'])) {
            $data['slug'] = Str::slug($data['judul']) . '-' . $promo->id;
        }

        $promo->update($data);
        return response()->json($promo);
    }

    public function destroy(Promo $promo)
    {
        $promo->delete();
        return response()->json(['message' => 'Promo/berita berhasil dihapus.']);
    }

    public function adminIndex()
    {
        return response()->json(Promo::latest()->get());
    }
}
