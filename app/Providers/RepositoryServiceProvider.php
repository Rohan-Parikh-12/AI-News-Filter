<?php

namespace App\Providers;

use App\Interfaces\CategoryRepositoryInterface;
use App\Repositories\CategoryRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
    }
}
