<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsSummary extends Model
{
    protected $table = 'news_summaries';

    protected $fillable = [
        'article_id', 'summary', 'ai_model',
        'prompt_tokens', 'completion_tokens', 'relevance_score',
    ];

    protected $casts = [
        'relevance_score'   => 'float',
        'prompt_tokens'     => 'integer',
        'completion_tokens' => 'integer',
    ];

    public function article() { return $this->belongsTo(NewsArticle::class, 'article_id'); }
}
