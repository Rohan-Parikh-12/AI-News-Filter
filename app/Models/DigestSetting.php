<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigestSetting extends Model
{
    protected $table = 'digest_settings';

    protected $fillable = [
        'user_id', 'digest_enabled', 'frequency',
        'send_time', 'timezone', 'max_articles',
    ];

    protected $casts = [
        'digest_enabled' => 'boolean',
        'max_articles'   => 'integer',
    ];

    public function user() { return $this->belongsTo(User::class); }
}
