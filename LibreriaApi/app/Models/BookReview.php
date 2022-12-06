<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookReview extends Model
{
    use HasFactory;
    protected $table = "book_reviews";
    protected $fillable = [
        "id",
        "commit",
        "edited",
        "book_id",
        "user_id"
    ];
    public $timestamps = false;

}