<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Series extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     *
     * @return MorphMany<Comment, Series>
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable', id: null, localKey: 'uuid');
    }
}
