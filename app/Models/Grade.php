<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SchoolClass extends Model
{
    use HasFactory;

    public function class(): HasOne
    {
        return $this->hasOne(Student::class);
    }
}
