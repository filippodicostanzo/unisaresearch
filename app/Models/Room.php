<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{

    use HasFactory;

    protected $table = 'rooms';

    protected $primaryKey = 'id';

    protected $fillable=[
        'id',
        'name',
        'address',
        'city',
        'longitude',
        'latitude',
        'description',
        'url',
        'visible',
        'edition'
    ];

    public function edition_fk()
    {
        return $this->belongsTo('App\Models\Edition', 'edition', 'id');
    }
}
