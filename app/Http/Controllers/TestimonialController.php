<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index()
    {
        return response()->json(Testimonial::where('status_publish', true)->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'foto' => 'nullable|string',
            'rating' => 'required|integer|min:1|max:5',
            'isi_review' => 'required|string',
            'status_publish' => 'boolean',
        ]);

        return response()->json(Testimonial::create($data), 201);
    }

    public function show(Testimonial $testimonial)
    {
        return response()->json($testimonial);
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $data = $request->validate([
            'nama' => 'sometimes|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'foto' => 'nullable|string',
            'rating' => 'sometimes|integer|min:1|max:5',
            'isi_review' => 'sometimes|string',
            'status_publish' => 'boolean',
        ]);

        $testimonial->update($data);
        return response()->json($testimonial);
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();
        return response()->json(['message' => 'Testimoni berhasil dihapus.']);
    }

    public function adminIndex()
    {
        return response()->json(Testimonial::latest()->get());
    }
}
