<?php

namespace App\Services;

use App\Models\NewsArticle;
use App\Models\NewsSummary;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Facades\Ai;

class AISummaryService
{
    public function summarize(NewsArticle $article): ?NewsSummary
    {
        try {
            $prompt = "Summarize the following news article in 2-3 concise sentences. Focus on the key facts only.\n\nTitle: {$article->title}\n\nContent: " . ($article->content ?? $article->description);

            $response = Ai::text($prompt);

            return NewsSummary::create([
                'article_id'        => $article->id,
                'summary'           => $response->text(),
                'ai_model'          => 'gemini-2.0-flash',
                'prompt_tokens'     => $response->usage()?->promptTokens ?? 0,
                'completion_tokens' => $response->usage()?->completionTokens ?? 0,
                'relevance_score'   => null,
            ]);
        } catch (\Exception $e) {
            Log::error("AI summarization failed for article #{$article->id}: " . $e->getMessage());
            return null;
        }
    }
}
