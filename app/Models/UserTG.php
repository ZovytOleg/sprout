<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserTG extends Model
{
    use HasFactory;

    protected $table = 'users_tg';

    public function role(): belongsTo
    {
        return $this->belongsTo(Role::class, 'user_role');
    }
}
