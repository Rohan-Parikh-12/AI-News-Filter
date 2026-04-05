<?php

namespace App\Observers;

use App\Jobs\SummarizeArticleJob;
use App\Models\NewsArticle;
use Illuminate\Support\Facades\Cache;

class ArticleObserver
{
    public function created(NewsArticle $article): void
    {
        $this->clearCache();
        SummarizeArticleJob::dispatch($article);
    }

    public function updated(NewsArticle $article): void  { $this->clearCache(); }
    public function deleted(NewsArticle $article): void  { $this->clearCache(); }
    public function restored(NewsArticle $article): void { $this->clearCache(); }

    private function clearCache(): void
    {
        Cache::forget('articles_all');

    }
}
