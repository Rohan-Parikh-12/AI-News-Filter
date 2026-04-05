<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Models\Category;
use App\Models\DigestLog;
use App\Models\NewsArticle;
use App\Models\NewsSummary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends ApiBaseController
{
    public function getDashboardStatistic(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->success([
            'categories' => [
                'total'    => Category::count(),
                'active'   => Category::where('status', '1')->count(),
            ],
            'articles' => [
                'total'     => NewsArticle::count(),
                'today'     => NewsArticle::whereDate('created_at', today())->count(),
                'summarized'=> NewsSummary::count(),
            ],
            'digest' => [
                'sent'    => DigestLog::where('status', 'sent')->count(),
                'failed'  => DigestLog::where('status', 'failed')->count(),
                'my_sent' => DigestLog::where('user_id', $user->id)->where('status', 'sent')->count(),
            ],
            'user' => [
                'my_categories' => $user->categories()->count(),
                'saved_articles'=> $user->savedArticles()->count(),
            ],
        ]);
    }

    public function getRecentArticles(Request $request): JsonResponse
    {
        $user = $request->user();

        $articles = NewsArticle::with(['category', 'summary'])
            ->where('status', '1')
            ->whereHas('category', fn($q) => $q->whereIn('id', $user->categories()->pluck('categories.id')))
            ->latest('published_at')
            ->take(5)
            ->get()
            ->map(fn($a) => [
                'id'          => $a->id,
                'title'       => $a->title,
                'summary'     => $a->summary?->summary,
                'category'    => $a->category?->name,
                'source'      => $a->source_name,
                'published_at'=> $a->published_at?->diffForHumans(),
                'url'         => $a->url,
                'image_url'   => $a->image_url,
            ]);

        return $this->success($articles);
    }

    public function getDigestHistory(Request $request): JsonResponse
    {
        $logs = DigestLog::where('user_id', $request->user()->id)
            ->latest()
            ->take(7)
            ->get()
            ->map(fn($l) => [
                'id'            => $l->id,
                'status'        => $l->status,
                'article_count' => $l->article_count,
                'sent_at'       => $l->sent_at?->format('d M Y, g:i A'),
            ]);

        return $this->success($logs);
    }
}
