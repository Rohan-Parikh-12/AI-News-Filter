<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface ArticleRepositoryInterface
{
    public function getArticleData(Request $request): array;
    public function getArticleDetails(int $id): array;
    public function articleSave(int $userId, int $articleId): bool;
    public function articleUnsave(int $userId, int $articleId): bool;
    public function articleStatistic(int $userId): array;
}
