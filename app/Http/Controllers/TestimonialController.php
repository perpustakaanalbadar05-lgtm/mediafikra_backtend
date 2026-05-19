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
            'foto' => 'nullable|image|max:2048',
            'rating' => 'required|integer|min:1|max:5',
            'isi_review' => 'required|string',
            'status_publish' => 'boolean',
        ]);

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('testimonials', 'public');
            $data['foto'] = '/storage/' . $path;
        }

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
            'foto' => 'nullable|image|max:2048',
            'rating' => 'sometimes|integer|min:1|max:5',
            'isi_review' => 'sometimes|string',
            'status_publish' => 'boolean',
        ]);

        if ($request->hasFile('foto')) {
            if ($testimonial->foto && str_starts_with($testimonial->foto, '/storage/')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete(str_replace('/storage/', '', $testimonial->foto));
            }
            $path = $request->file('foto')->store('testimonials', 'public');
            $data['foto'] = '/storage/' . $path;
        }

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
