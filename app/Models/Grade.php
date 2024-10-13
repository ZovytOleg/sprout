<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Grade extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'teacher_id'
    ];

    public function class(): HasOne
    {
        return $this->hasOne(Student::class);
    }

}
