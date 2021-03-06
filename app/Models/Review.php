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
        'result',
        'review',
        'post',
        'supervisor',
        ];

    public function user_fk() {
        return $this->belongsTo('App\Models\User', 'supervisor', 'id');
    }

    public function post_fk() {
        return $this->belongsTo('App\Models\Post', 'post', 'id');
    }

}
