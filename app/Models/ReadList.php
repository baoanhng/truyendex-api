<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadList extends Model
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
     * @return BelongsTo<Series, ReadList>
     */
    public function series()
    {
        return $this->belongsTo(Series::class, 'series_uuid', 'uuid');
    }
}
