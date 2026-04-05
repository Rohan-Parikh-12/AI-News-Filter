<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait DataTableConfiguration
{
    protected function applySearch(Builder $query, ?string $search, array $columns): Builder
    {
        if (!$search) return $query;

        return $query->where(function ($q) use ($search, $columns) {
            foreach ($columns as $col) {
                $q->orWhere($col, 'like', '%' . $search . '%');
            }
        });
    }

    protected function applySorting(Builder $query, ?string $sortBy, ?string $orderBy): Builder
    {
        if ($sortBy) {
            $query->orderBy($sortBy, $orderBy ?? 'asc');
        }

        return $query;
    }

    protected function applyPagination(Builder $query, int $page, int $perPage): array
    {
        $total = $query->count();
        $data  = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return ['data' => $data, 'total' => $total];
    }

    protected function executeQuery(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
