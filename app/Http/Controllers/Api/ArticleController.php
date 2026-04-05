<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Interfaces\ArticleRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends ApiBaseController
{
    public function __construct(private ArticleRepositoryInterface $repo) {}

    public function getArticleData(Request $request): JsonResponse
    {
        $this->authorizeUser($request->user(), 'articles-view');
        $result = $this->repo->getArticleData($request);
        return response()->json(['data' => $result['data'], 'total' => $result['total']]);
    }

    public function getArticleDetails(Request $request): JsonResponse
    {
        $this->authorizeUser($request->user(), 'articles-view');
        return $this->success($this->repo->getArticleDetails((int) $request->id));
    }

    public function articleSave(Request $request): JsonResponse
    {
        $this->authorizeUser($request->user(), 'articles-save');
        $request->validate(['article_id' => 'required|integer|exists:news_articles,id']);
        $this->repo->articleSave($request->user()->id, $request->article_id);
        return $this->success(null, 'Article saved.');
    }

    public function articleUnsave(Request $request): JsonResponse
    {
        $this->authorizeUser($request->user(), 'articles-save');
        $request->validate(['article_id' => 'required|integer|exists:news_articles,id']);
        $this->repo->articleUnsave($request->user()->id, $request->article_id);
        return $this->success(null, 'Article unsaved.');
    }

    public function articleStatistic(Request $request): JsonResponse
    {
        $this->authorizeUser($request->user(), 'articles-view');
        return $this->success($this->repo->articleStatistic($request->user()->id));
    }
}
