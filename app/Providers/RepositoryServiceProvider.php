<?php

namespace App\Providers;

use App\Interfaces\ArticleRepositoryInterface;
use App\Interfaces\CategoryRepositoryInterface;
use App\Repositories\ArticleRepository;
use App\Repositories\CategoryRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(ArticleRepositoryInterface::class, ArticleRepository::class);
    }
}
