<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;


    protected $table = 'events';

    protected $primaryKey = 'id';

    protected $fillable=[
        'id',
        'title',
        'type',
        'authors',
        'start',
        'end',
        'description',
        'room',
        'edition',
        'active'
        ];

    public function room_fk()
    {
        return $this->belongsTo('App\Models\Room', 'room', 'id');
    }

    public function edition_fk()
    {
        return $this->belongsTo('App\Models\Edition', 'edition', 'id');
    }
}
