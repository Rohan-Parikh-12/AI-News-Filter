<?php

namespace App\Models;

use App\Traits\CommonTrait;
use App\Traits\HasRelatedDataTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes, HasRelatedDataTrait, CommonTrait;

    const STATUS_ARCHIVE  = '0';
    const STATUS_ACTIVE   = '1';
    const STATUS_INACTIVE = '2';

    protected $table = 'categories';

    protected $fillable = [
        'name', 'slug', 'api_keyword', 'description',
        'icon', 'sort_order', 'status',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function scopeActive($q) { return $q->where('status', self::STATUS_ACTIVE); }

    public function articles() { return $this->hasMany(NewsArticle::class, 'category_id'); }
    public function users()    { return $this->belongsToMany(User::class, 'user_categories'); }

    public function getRelationshipChecks(): array
    {
        return [
            'articles' => 'News Articles',
            'users'    => 'User Interests',
        ];
    }
}
