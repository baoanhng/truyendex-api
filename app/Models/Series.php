<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'md_updated_at' => 'datetime',
            'last_chapter_updated_at' => 'datetime',
        ];
    }

    /**
     *
     * @return MorphMany<Comment, Series>
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable', id: null, localKey: 'uuid');
    }

    /**
     * Get the chapters for the series.
     * @return HasMany<Chapter, Series>
     */
    public function chapters()
    {
        return $this->hasMany(Chapter::class, 'series_uuid', 'uuid');
    }
}
