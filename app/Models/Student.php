<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use CrudTrait;
    use HasFactory;
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'class_id'
    ];
    protected $hidden = [
        'token_id'
    ];

    protected $table = 'students';
    protected $primaryKey = 'id';
    public function tokenTG(): HasOne
    {
        return $this->hasOne(Token::class);
    }
}
