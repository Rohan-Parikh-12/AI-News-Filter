<?php

namespace App\Console\Commands;

use App\Services\NewsService;
use Illuminate\Console\Command;

class FetchNewsCommand extends Command
{
    protected $signature   = 'news:fetch';
    protected $description = 'Fetch latest news articles from NewsAPI and GNews for all active categories';

    public function handle(NewsService $service): int
    {
        $this->info('Fetching news articles...');

        $results = $service->fetchAll();

        $this->info("✓ Fetched:  {$results['fetched']} new articles");
        $this->info("✓ Skipped:  {$results['skipped']} duplicates");

        if (!empty($results['errors'])) {
            foreach ($results['errors'] as $error) {
                $this->error("✗ {$error}");
            }
        }

        return self::SUCCESS;
    }
}
