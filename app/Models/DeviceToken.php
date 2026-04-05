<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    protected $table    = 'device_tokens';
    protected $fillable = ['user_id', 'token', 'platform', 'device_name', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public function user() { return $this->belongsTo(User::class); }
}
