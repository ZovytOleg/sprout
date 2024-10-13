<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ScheduleLessons extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_id',
        'day'
    ];

    protected $casts = [
        'lessons' => 'collection',
    ];

    protected $table = 'schedule_lessons';
    protected $primaryKey = 'id';

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }
}
