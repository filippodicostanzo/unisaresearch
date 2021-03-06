<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;

    protected $table = 'authors';

    protected $primaryKey = 'id';

    protected $fillable=[
        'id',
        'firstname',
        'lastname',
        'email',
        'user_id'
    ];
    public function users()
    {
        return $this->belongsToMany('App\Models\User');
    }
}
