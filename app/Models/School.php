<?php

namespace App\Models;

use App\Models\SupabaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends SupabaseModel
{
    protected $table = 'schools';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'director_name'
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string'
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
