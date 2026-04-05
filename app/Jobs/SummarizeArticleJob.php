<?php

namespace App\Jobs;

use App\Models\NewsArticle;
use App\Services\AISummaryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SummarizeArticleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;
    public int $timeout = 120;

    public function __construct(public NewsArticle $article) {}

    public function handle(AISummaryService $ai): void
    {
        if ($this->article->summary()->exists()) return;
        $ai->summarize($this->article);
    }

    public function failed(\Throwable $e): void
    {
        Log::error("SummarizeArticleJob failed for article #{$this->article->id}: " . $e->getMessage());
    }
}
