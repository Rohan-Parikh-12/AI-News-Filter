<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /**
     * Returns CASL ability rules for the Vue frontend.
     * admin role → manage all
     * other roles → map each Spatie permission to a CASL rule
     */
    public function abilityRules(): array
    {
        if ($this->hasRole('admin')) {
            return [['action' => 'manage', 'subject' => 'all']];
        }

        // Map permission name → CASL [action, subject]
        $map = [
            'categories-view'   => ['read',   'Categories'],
            'categories-create' => ['create', 'Categories'],
            'categories-edit'   => ['update', 'Categories'],
            'categories-delete' => ['delete', 'Categories'],
            'categories-status' => ['update', 'Categories'],
            'articles-view'     => ['read',   'Articles'],
            'articles-save'     => ['create', 'Articles'],
            'digest-view'       => ['read',   'Digest'],
            'digest-settings'   => ['update', 'Digest'],
            'preferences-view'  => ['read',   'Preferences'],
            'preferences-edit'  => ['update', 'Preferences'],
        ];

        $rules = [['action' => 'read', 'subject' => 'Dashboard']];

        foreach ($this->getAllPermissions()->pluck('name') as $perm) {
            if (isset($map[$perm])) {
                $rules[] = ['action' => $map[$perm][0], 'subject' => $map[$perm][1]];
            }
        }

        return array_values(array_unique($rules, SORT_REGULAR));
    }
}
