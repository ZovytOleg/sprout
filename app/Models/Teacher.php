<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'subject',
        'class_leader'
    ];
    protected $hidden = [
        'token_id',
    ];

    public function tokenTG(): HasOne
    {
        return $this->hasOne(Token::class);
    }
}
