<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chapter extends Model
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
        ];
    }

    /**
     *
     * @return BelongsTo<Series, Chapter>
     */
    public function series()
    {
        return $this->belongsTo(Series::class, 'series_uuid', 'uuid');
    }
}
