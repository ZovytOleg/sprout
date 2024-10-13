<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Test extends Model
{
    use HasFactory;

    protected $table = 'schedule_lessons';
    protected $primaryKey = 'id';

    protected $fillable = [
        'grade_id',
        'day'
    ];

    protected $casts = [
        'lessons' => 'collection',
    ];

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

}
