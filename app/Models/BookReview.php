<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BookReview extends Model {
    protected $guarded = [];
    public function book() { return $this->belongsTo(Book::class); }
}
