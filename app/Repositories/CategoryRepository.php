<?php

namespace App\Repositories;

use App\Interfaces\CategoryRepositoryInterface;
use App\Models\Category;
use App\Traits\DataTableConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryRepository implements CategoryRepositoryInterface
{
    use DataTableConfiguration;

    public function getCategoryData(Request $request): array
    {
        $query = Category::withTrashed(false)->withCount('articles');

        $query = $this->applySearch($query, $request->searchQuery, ['name', 'slug', 'api_keyword']);
        $query = $this->applySorting($query, $request->sortBy ?? 'sort_order', $request->orderBy ?? 'asc');

        $result = $this->applyPagination($query, (int)($request->page ?? 1), (int)($request->itemsPerPage ?? 10));

        $result['data'] = $result['data']->map(fn($c) => $this->format($c));

        return $result;
    }

    public function getCategoryDetails(int $id): Category
    {
        return Category::withCount('articles')->findOrFail($id);
    }

    public function manageCategory(array $data): Category
    {
        return $this->executeQuery(function () use ($data) {
            if (!empty($data['id'])) {
                $category = Category::findOrFail($data['id']);
                $category->update($data);
            } else {
                $data['slug'] = Str::slug($data['name']);
                $category = Category::create($data);
            }
            return $category->fresh();
        });
    }

    public function categoryDelete(array $ids): bool
    {
        return $this->executeQuery(function () use ($ids) {
            foreach ($ids as $id) {
                $category = Category::findOrFail($id);
                if ($category->getHasRelatedData()) {
                    throw new \Exception("Category '{$category->name}' has related data and cannot be deleted.");
                }
                $category->delete();
            }
            return true;
        });
    }

    public function categoryStatusChange(int $id, string $status): Category
    {
        return $this->executeQuery(function () use ($id, $status) {
            $category = Category::findOrFail($id);
            $category->update(['status' => $status]);
            return $category->fresh();
        });
    }

    public function categoryStatistic(): array
    {
        return [
            'total'    => Category::count(),
            'active'   => Category::where('status', Category::STATUS_ACTIVE)->count(),
            'inactive' => Category::where('status', Category::STATUS_INACTIVE)->count(),
            'archive'  => Category::where('status', Category::STATUS_ARCHIVE)->count(),
        ];
    }

    private function format(Category $c): array
    {
        return [
            'id'               => $c->id,
            'name'             => $c->name,
            'slug'             => $c->slug,
            'api_keyword'      => $c->api_keyword,
            'description'      => $c->description,
            'icon'             => $c->icon,
            'sort_order'       => $c->sort_order,
            'status'           => $c->status,
            'articles_count'   => $c->articles_count,
            'has_related_data' => $c->getHasRelatedData(),
            'created_at'       => $c->created_at?->format('d M Y'),
        ];
    }
}
