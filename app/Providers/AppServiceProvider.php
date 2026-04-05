<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\NewsArticle;
use App\Models\User;
use App\Observers\ArticleObserver;
use App\Observers\CategoryObserver;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(RepositoryServiceProvider::class);
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Category::observe(CategoryObserver::class);
        NewsArticle::observe(ArticleObserver::class);
        User::observe(UserObserver::class);
    }
}
