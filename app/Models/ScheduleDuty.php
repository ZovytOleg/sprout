<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ScheduleDuty extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'grade_id',
        'senior_id',
        'yard_id'
    ];

    protected $casts = [
        'dates' => 'collection',
        'first_floor_id' => 'collection',
        'second_floor_id' => 'collection',
        'third_floor_id' => 'collection',
    ];

    protected $table = 'schedule_duty';
    protected $primaryKey = 'id';

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function senior(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function yard(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function firstf(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

}
