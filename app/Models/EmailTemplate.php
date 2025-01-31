<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $table = 'email_templates';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'subject',
        'body',
        'template',
        'active'
    ];

    /**
     * Gli attributi che dovrebbero essere convertiti in tipi nativi.
     *
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Gli attributi che dovrebbero avere un valore predefinito.
     *
     * @var array
     */
    protected $attributes = [
        'active' => true,
    ];
}
