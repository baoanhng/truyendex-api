<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // 'commentable_type',
        'commentable_id',
    ];

    /**
     *
     * @return BelongsTo<User, Comment>
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     *
     * @return MorphTo<Model, Comment>
     */
    public function commentable()
    {
        return $this->morphTo();
    }

    /**
     *
     * @return HasMany<Comment, Comment>
     */
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id', 'id');
    }
}
