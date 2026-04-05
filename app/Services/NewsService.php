<?php

namespace App\Services;

use App\Models\Category;
use App\Models\NewsArticle;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsService
{
    public function fetchAll(): array
    {
        $categories = Category::where('status', '1')->get();
        $results    = ['fetched' => 0, 'skipped' => 0, 'errors' => []];

        foreach ($categories as $category) {
            try {
                $articles = $this->fetchFromNewsApi($category->api_keyword);

                if (empty($articles)) {
                    $articles = $this->fetchFromGNews($category->api_keyword);
                }

                foreach ($articles as $article) {
                    $stored = $this->store($article, $category->id);
                    $stored ? $results['fetched']++ : $results['skipped']++;
                }
            } catch (\Exception $e) {
                $results['errors'][] = "Category {$category->name}: " . $e->getMessage();
                Log::error("NewsService error for {$category->name}: " . $e->getMessage());
            }
        }

        return $results;
    }

    private function fetchFromNewsApi(string $keyword): array
    {
        $key = config('services.newsapi.key');
        if (!$key) return [];

        $response = Http::timeout(15)
            ->withoutVerifying()
            ->get('https://newsapi.org/v2/everything', [
            'q'        => $keyword,
            'language' => 'en',
            'sortBy'   => 'publishedAt',
            'pageSize' => 10,
            'apiKey'   => $key,
        ]);

        if (!$response->successful()) return [];

        return collect($response->json('articles', []))
            ->filter(fn($a) => !empty($a['url']) && ($a['title'] ?? '') !== '[Removed]')
            ->map(fn($a) => [
                'external_id'  => md5($a['url']),
                'source_api'   => 'newsapi',
                'title'        => $a['title'] ?? '',
                'description'  => $a['description'] ?? null,
                'content'      => $a['content'] ?? null,
                'url'          => $a['url'],
                'image_url'    => $a['urlToImage'] ?? null,
                'source_name'  => $a['source']['name'] ?? null,
                'author'       => $a['author'] ?? null,
                'published_at' => isset($a['publishedAt']) ? date('Y-m-d H:i:s', strtotime($a['publishedAt'])) : null,
            ])
            ->values()
            ->toArray();
    }

    private function fetchFromGNews(string $keyword): array
    {
        $key = config('services.gnews.key');
        if (!$key) return [];

        $response = Http::timeout(15)
            ->withoutVerifying()
            ->get('https://gnews.io/api/v4/search', [
            'q'      => $keyword,
            'lang'   => 'en',
            'max'    => 10,
            'apikey' => $key,
        ]);

        if (!$response->successful()) return [];

        return collect($response->json('articles', []))
            ->filter(fn($a) => !empty($a['url']))
            ->map(fn($a) => [
                'external_id'  => md5($a['url']),
                'source_api'   => 'gnews',
                'title'        => $a['title'] ?? '',
                'description'  => $a['description'] ?? null,
                'content'      => $a['content'] ?? null,
                'url'          => $a['url'],
                'image_url'    => $a['image'] ?? null,
                'source_name'  => $a['source']['name'] ?? null,
                'author'       => null,
                'published_at' => isset($a['publishedAt']) ? date('Y-m-d H:i:s', strtotime($a['publishedAt'])) : null,
            ])
            ->values()
            ->toArray();
    }

    private function store(array $data, int $categoryId): bool
    {
        if (NewsArticle::where('external_id', $data['external_id'])->exists()) {
            return false;
        }

        NewsArticle::create(array_merge($data, [
            'category_id' => $categoryId,
            'status'      => '1',
        ]));

        return true;
    }
}
