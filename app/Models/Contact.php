<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = ['name', 'email', 'message', 'role'];

    public $timestamps = false; // table only has created_at, no updated_at
}
