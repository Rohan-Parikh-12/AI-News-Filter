<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Category\CategoryRequest;
use App\Interfaces\CategoryRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends ApiBaseController
{
    public function __construct(private CategoryRepositoryInterface $repo) {}

    public function getCategoryData(Request $request): JsonResponse
    {
        $this->authorizeUser($request->user(), 'categories-view');
        $result = $this->repo->getCategoryData($request);
        return $this->success($result['data'], 'Categories fetched.', 200)->header('X-Total', $result['total'])
            ?: response()->json(['data' => $result['data'], 'total' => $result['total']]);
    }

    public function getCategoryDetails(Request $request): JsonResponse
    {
        $this->authorizeUser($request->user(), 'categories-view');
        $category = $this->repo->getCategoryDetails((int) $request->id);
        return $this->success($category);
    }

    public function manageCategory(CategoryRequest $request): JsonResponse
    {
        $permission = $request->filled('id') ? 'categories-edit' : 'categories-create';
        $this->authorizeUser($request->user(), $permission);
        $category = $this->repo->manageCategory($request->validated());
        $isUpdate = $request->filled('id');
        return $this->success($category, 'Category ' . ($isUpdate ? 'updated' : 'created') . ' successfully.', $isUpdate ? 200 : 201);
    }

    public function categoryDelete(Request $request): JsonResponse
    {
        $this->authorizeUser($request->user(), 'categories-delete');
        $ids = (array) ($request->ids ?? [$request->id]);
        $this->repo->categoryDelete($ids);
        return $this->success(null, 'Category deleted successfully.');
    }

    public function categoryStatusChange(Request $request): JsonResponse
    {
        $this->authorizeUser($request->user(), 'categories-status');
        $request->validate(['id' => 'required|integer', 'newStatus' => 'required|in:0,1,2']);
        $category = $this->repo->categoryStatusChange((int) $request->id, $request->newStatus);
        return $this->success($category, 'Status updated successfully.');
    }

    public function categoryStatistic(Request $request): JsonResponse
    {
        $this->authorizeUser($request->user(), 'categories-view');
        return $this->success($this->repo->categoryStatistic());
    }

    public function categoryHistory(Request $request): JsonResponse
    {
        $this->authorizeUser($request->user(), 'categories-view');
        // Activity log — placeholder until spatie/laravel-activitylog is added
        return $this->success([], 'History fetched.');
    }
}
