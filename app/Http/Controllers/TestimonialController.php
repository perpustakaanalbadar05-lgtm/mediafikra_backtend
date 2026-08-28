<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TestimonialController extends Controller
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
        return response()->json(Testimonial::where('status_publish', true)->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'           => 'required|string|max:255',
            'jabatan'        => 'nullable|string|max:255',
            'foto'           => 'nullable|image|max:2048',
            'rating'         => 'required|integer|min:1|max:5',
            'isi_review'     => 'required|string',
            'status_publish' => 'boolean',
        ]);

        if ($request->hasFile('foto')) {
            $data['foto'] = $this->saveImage($request->file('foto'), 'testimonials');
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
            'nama'           => 'sometimes|string|max:255',
            'jabatan'        => 'nullable|string|max:255',
            'foto'           => 'nullable|image|max:2048',
            'rating'         => 'sometimes|integer|min:1|max:5',
            'isi_review'     => 'sometimes|string',
            'status_publish' => 'boolean',
        ]);

        if ($request->hasFile('foto')) {
            $this->deleteImage($testimonial->foto);
            $data['foto'] = $this->saveImage($request->file('foto'), 'testimonials');
        }

        $testimonial->update($data);
        return response()->json($testimonial);
    }

    public function destroy(Testimonial $testimonial)
    {
        $this->deleteImage($testimonial->foto);
        $testimonial->delete();
        return response()->json(['message' => 'Testimoni berhasil dihapus.']);
    }

    public function adminIndex()
    {
        return response()->json(Testimonial::latest()->get());
    }
}
