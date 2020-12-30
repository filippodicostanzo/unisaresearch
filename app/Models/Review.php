<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';

    protected $primaryKey = 'id';

    protected $fillable=[
        'id',
        'field_1',
        'field_2',
        'field_3',
        'review',
        'post',
        'supervisor',
        ];

}
