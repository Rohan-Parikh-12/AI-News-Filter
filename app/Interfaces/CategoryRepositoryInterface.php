<?php

namespace App\Interfaces;

use App\Models\Category;
use Illuminate\Http\Request;

interface CategoryRepositoryInterface
{
    public function getCategoryData(Request $request): array;
    public function getCategoryDetails(int $id): Category;
    public function manageCategory(array $data): Category;
    public function categoryDelete(array $ids): bool;
    public function categoryStatusChange(int $id, string $status): Category;
    public function categoryStatistic(): array;
}
