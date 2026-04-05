<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigestLog extends Model
{
    protected $table = 'digest_logs';

    protected $fillable = [
        'user_id', 'status', 'article_ids',
        'article_count', 'error_message', 'sent_at',
    ];

    protected $casts = [
        'article_ids' => 'array',
        'sent_at'     => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
}
