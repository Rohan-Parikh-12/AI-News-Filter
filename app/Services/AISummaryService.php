<?php

namespace App\Services;

use App\Models\NewsArticle;
use App\Models\NewsSummary;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AISummaryService
{
    public function summarize(NewsArticle $article): ?NewsSummary
    {
        try {
            $prompt = "Summarize the following news article in 2-3 concise sentences. Focus on the key facts only.\n\nTitle: {$article->title}\n\nContent: " . ($article->content ?? $article->description ?? $article->title);

            $summary = $this->callGemini($prompt);

            if (!$summary) return null;

            return NewsSummary::create([
                'article_id'        => $article->id,
                'summary'           => $summary,
                'ai_model'          => 'gemini-2.0-flash',
                'prompt_tokens'     => 0,
                'completion_tokens' => 0,
                'relevance_score'   => null,
            ]);
        } catch (\Exception $e) {
            Log::error("AI summarization failed for article #{$article->id}: " . $e->getMessage());
            return null;
        }
    }

    private function callGemini(string $prompt): ?string
    {
        $key = config('services.gemini.key') ?? env('GEMINI_API_KEY');

        if (!$key) {
            Log::warning('GEMINI_API_KEY not set.');
            return null;
        }

        $response = Http::withoutVerifying()
            ->timeout(30)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$key}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 200,
                    'temperature'     => 0.3,
                ],
            ]);

        if (!$response->successful()) {
            Log::error('Gemini API error: ' . $response->body());
            return null;
        }

        return $response->json('candidates.0.content.parts.0.text');
    }
}
