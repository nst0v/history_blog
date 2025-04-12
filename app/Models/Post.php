<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'slug', 'image', 'category', 'tags', 'status', 'meta_title', 'meta_description', 'body',
    ];

    // Связь с пользователем (один пост принадлежит одному пользователю)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
