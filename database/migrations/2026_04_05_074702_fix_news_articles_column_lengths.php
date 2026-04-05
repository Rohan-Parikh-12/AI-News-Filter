<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_articles', function (Blueprint $table) {
            $table->text('url')->change();
            $table->text('image_url')->nullable()->change();
            $table->string('title', 500)->change();
            $table->string('author', 500)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('news_articles', function (Blueprint $table) {
            $table->string('url')->change();
            $table->string('image_url')->nullable()->change();
            $table->string('title')->change();
            $table->string('author')->nullable()->change();
        });
    }
};
