<?php

namespace App\Http\Controllers;

use App\Models\BookReview;
use Illuminate\Http\Request;

class BookReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = BookReview::query()->with('book');
        if ($request->book_id) {
            $query->where('book_id', $request->book_id);
        }
        return response()->json($query->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'reviewer_name' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);
        
        $review = BookReview::create($validated);
        return response()->json($review, 201);
    }

    public function destroy(BookReview $bookReview)
    {
        $bookReview->delete();
        return response()->json(['message' => 'Review deleted']);
    }
}
