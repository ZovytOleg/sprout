<?php

namespace App\Models;

use App\MoonShine\Resources\ScheduleDutyResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'grade_id',
        'is_verified',
    ];
    protected $hidden = [
        'token',
    ];

    protected $casts = [
        'subjects' => 'collection',
    ];

    protected $table = 'teachers';
    protected $primaryKey = 'id';

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }
/*    public function subjects(): HasMany
    {
        return $this->HasMany(Subject::class);
    }*/

        public function subjects(): BelongsTo
    {
        return $this->BelongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(ScheduleDuty::class, 'first_floor_id', 'id');
    }

    public function subjectss(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'teacher_id', 'id');
    }
}
