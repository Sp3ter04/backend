<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageFeedback extends SupabaseModel
{
    protected $table = 'page_feedback';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_type',
        'page_path',
        'page_label',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'id'         => 'string',
            'user_id'    => 'string',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
