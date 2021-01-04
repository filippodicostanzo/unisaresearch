<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $table = 'posts';

    protected $primaryKey = 'id';

    protected $fillable=[
        'id',
        'title',
        'authors',
        'template',
        'category',
        'created',
        'edit',
        'state',
        'supervisors',
        'tags',
        'pdf',
        'field_1',
        'field_2',
        'field_3',
        'field_4',
        'field_5',
        'field_6',
        'field_7',
        'field_8',
        'field_9',
        'latest_modify',
    ];

    public function category_fk()
    {
        return $this->belongsTo('App\Models\Category', 'category', 'id');
    }

    public function template_fk()
    {
        return $this->belongsTo('App\Models\Template', 'template', 'id');
    }

    public function state_fk()
    {
        return $this->belongsTo('App\Models\Status', 'state', 'id');
    }

    public function authors()
    {
        return $this->belongsToMany('App\Models\Author');
    }

    public function user_fk() {
        return $this->belongsTo('App\Models\User', 'created', 'id');
    }

    public function users()
    {
        return $this->belongsToMany('App\Models\User');
    }


}
