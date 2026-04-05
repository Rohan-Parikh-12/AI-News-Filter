<?php

namespace App\Repositories;

use App\Interfaces\ArticleRepositoryInterface;
use App\Models\NewsArticle;
use App\Models\SavedArticle;
use App\Traits\DataTableConfiguration;
use Illuminate\Http\Request;

class ArticleRepository implements ArticleRepositoryInterface
{
    use DataTableConfiguration;

    public function getArticleData(Request $request): array
    {
        $query = NewsArticle::with(['category', 'summary'])
            ->where('status', '1');

        if ($request->filled('category_id'))
            $query->where('category_id', $request->category_id);

        if ($request->filled('saved') && $request->user())
            $query->whereHas('savedBy', fn($q) => $q->where('user_id', $request->user()->id));

        $query = $this->applySearch($query, $request->searchQuery, ['title', 'description', 'source_name']);
        $query = $this->applySorting($query, $request->sortBy ?? 'published_at', $request->orderBy ?? 'desc');

        $result = $this->applyPagination($query, (int)($request->page ?? 1), (int)($request->itemsPerPage ?? 10));

        $userId = $request->user()?->id;
        $savedIds = $userId
            ? SavedArticle::where('user_id', $userId)->pluck('article_id')->toArray()
            : [];
        $result['data'] = $result['data']->map(fn($a) => $this->format($a, $savedIds));

        return $result;
    }

    public function getArticleDetails(int $id): array
    {
        $article = NewsArticle::with(['category', 'summary'])->findOrFail($id);
        return $this->format($article, []);
    }

    public function articleSave(int $userId, int $articleId): bool
    {
        SavedArticle::firstOrCreate(['user_id' => $userId, 'article_id' => $articleId]);
        return true;
    }

    public function articleUnsave(int $userId, int $articleId): bool
    {
        SavedArticle::where('user_id', $userId)->where('article_id', $articleId)->delete();
        return true;
    }

    public function articleStatistic(int $userId): array
    {
        return [
            'total'     => NewsArticle::where('status', '1')->count(),
            'today'     => NewsArticle::where('status', '1')->whereDate('created_at', today())->count(),
            'saved'     => SavedArticle::where('user_id', $userId)->count(),
            'summarized'=> \App\Models\NewsSummary::count(),
        ];
    }

    private function format(NewsArticle $a, array $savedIds): array
    {
        return [
            'id'           => $a->id,
            'title'        => $a->title,
            'description'  => $a->description,
            'url'          => $a->url,
            'image_url'    => $a->image_url,
            'source_name'  => $a->source_name,
            'author'       => $a->author,
            'published_at' => $a->published_at?->format('d M Y'),
            'category'     => $a->category?->name,
            'category_id'  => $a->category_id,
            'summary'      => $a->summary?->summary,
            'is_saved'     => in_array($a->id, $savedIds),
            'status'       => $a->status,
        ];
    }
}
