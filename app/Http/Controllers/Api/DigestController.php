<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Models\Category;
use App\Models\DigestLog;
use App\Models\DigestSetting;
use App\Models\NewsArticle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DigestController extends ApiBaseController
{
    // GET /api/getDigestToday
    // Returns today's articles grouped by category for the logged-in user
    public function getDigestToday(Request $request): JsonResponse
    {
        $user = $request->user();

        $digestSetting = DigestSetting::firstOrCreate(
            ['user_id' => $user->id],
            ['digest_enabled' => true, 'frequency' => 'daily', 'send_time' => '07:00:00', 'timezone' => 'UTC', 'max_articles' => 5]
        );

        $userCategoryIds = $user->categories()->pluck('categories.id');

        if ($userCategoryIds->isEmpty()) {
            return $this->success([
                'articles'       => [],
                'total'          => 0,
                'digest_setting' => $this->formatSetting($digestSetting),
                'message'        => 'no_categories',
            ]);
        }

        $articles = NewsArticle::with(['category', 'summary'])
            ->where('status', '1')
            ->whereIn('category_id', $userCategoryIds)
            ->latest('published_at')
            ->take($digestSetting->max_articles)
            ->get();

        $savedIds = $user->savedArticles()->pluck('news_articles.id')->toArray();

        $grouped = $articles->groupBy(fn($a) => $a->category?->name ?? 'General')
            ->map(fn($group, $catName) => [
                'category' => $catName,
                'articles' => $group->map(fn($a) => $this->formatArticle($a, $savedIds))->values(),
            ])
            ->values();

        return $this->success([
            'articles'       => $grouped,
            'total'          => $articles->count(),
            'digest_setting' => $this->formatSetting($digestSetting),
            'message'        => $articles->isEmpty() ? 'no_articles' : 'ok',
        ]);
    }

    // GET /api/getDigestData — digest send history
    public function getDigestData(Request $request): JsonResponse
    {
        $user  = $request->user();
        $page  = (int) $request->get('page', 1);
        $perPage = (int) $request->get('itemsPerPage', 10);

        $total = DigestLog::where('user_id', $user->id)->count();
        $logs  = DigestLog::where('user_id', $user->id)
            ->latest()
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(fn($l) => [
                'id'            => $l->id,
                'status'        => $l->status,
                'article_count' => $l->article_count,
                'sent_at'       => $l->sent_at?->format('d M Y, g:i A'),
                'error_message' => $l->error_message,
            ]);

        return response()->json(['data' => $logs, 'total' => $total]);
    }

    // POST /api/manageDigestSettings
    public function manageDigestSettings(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'digest_enabled' => 'boolean',
            'frequency'      => 'in:daily,weekly',
            'send_time'      => 'date_format:H:i',
            'timezone'       => 'string|max:100',
            'max_articles'   => 'integer|min:1|max:20',
        ]);

        $setting = DigestSetting::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        return $this->success($this->formatSetting($setting), 'Digest settings saved.');
    }

    // GET /api/getDigestStatistic
    public function getDigestStatistic(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->success([
            'total_sent'    => DigestLog::where('user_id', $user->id)->where('status', 'sent')->count(),
            'total_failed'  => DigestLog::where('user_id', $user->id)->where('status', 'failed')->count(),
            'my_categories' => $user->categories()->count(),
            'available'     => NewsArticle::where('status', '1')
                ->whereIn('category_id', $user->categories()->pluck('categories.id'))
                ->count(),
        ]);
    }

    private function formatArticle(NewsArticle $a, array $savedIds): array
    {
        return [
            'id'           => $a->id,
            'title'        => $a->title,
            'description'  => $a->description,
            'url'          => $a->url,
            'image_url'    => $a->image_url,
            'source_name'  => $a->source_name,
            'published_at' => $a->published_at?->diffForHumans(),
            'summary'      => $a->summary?->summary,
            'is_saved'     => in_array($a->id, $savedIds),
        ];
    }

    private function formatSetting(DigestSetting $s): array
    {
        return [
            'digest_enabled' => $s->digest_enabled,
            'frequency'      => $s->frequency,
            'send_time'      => substr($s->send_time, 0, 5),
            'timezone'       => $s->timezone,
            'max_articles'   => $s->max_articles,
        ];
    }
}
