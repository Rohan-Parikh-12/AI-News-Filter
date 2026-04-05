<?php

namespace App\Console\Commands;

use App\Models\NewsArticle;
use App\Services\AISummaryService;
use Illuminate\Console\Command;

class SummarizeArticlesCommand extends Command
{
    protected $signature   = 'news:summarize';
    protected $description = 'AI-summarize all articles that do not have a summary yet';

    public function handle(AISummaryService $ai): int
    {
        $articles = NewsArticle::whereDoesntHave('summary')
            ->where('status', '1')
            ->latest()
            ->take(20)
            ->get();

        if ($articles->isEmpty()) {
            $this->info('No articles to summarize.');
            return self::SUCCESS;
        }

        $this->info("Summarizing {$articles->count()} articles...");
        $bar = $this->output->createProgressBar($articles->count());
        $bar->start();

        $done = 0;
        foreach ($articles as $article) {
            $result = $ai->summarize($article);
            if ($result) $done++;
            $bar->advance();
            sleep(3); // respect Gemini free tier rate limit (15 RPM)
        }

        $bar->finish();
        $this->newLine();
        $this->info("✓ Summarized {$done} / {$articles->count()} articles.");

        return self::SUCCESS;
    }
}
