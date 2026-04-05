<?php

namespace App\Models;

use App\Traits\CommonTrait;
use App\Traits\HasRelatedDataTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsArticle extends Model
{
    use HasFactory, SoftDeletes, HasRelatedDataTrait, CommonTrait;

    const STATUS_ARCHIVE  = '0';
    const STATUS_ACTIVE   = '1';
    const STATUS_INACTIVE = '2';

    protected $table = 'news_articles';

    protected $fillable = [
        'category_id', 'external_id', 'source_api', 'title',
        'description', 'content', 'url', 'image_url',
        'source_name', 'author', 'published_at', 'status',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function scopeActive($q) { return $q->where('status', self::STATUS_ACTIVE); }

    public function category()  { return $this->belongsTo(Category::class); }
    public function summary()   { return $this->hasOne(NewsSummary::class, 'article_id'); }
    public function savedBy()   { return $this->belongsToMany(User::class, 'saved_articles'); }

    public function getRelationshipChecks(): array
    {
        return ['summary' => 'AI Summary'];
    }
}
