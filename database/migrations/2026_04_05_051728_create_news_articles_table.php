<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('external_id')->unique();
            $table->string('source_api'); // newsapi | gnews
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('url');
            $table->string('image_url')->nullable();
            $table->string('source_name')->nullable();
            $table->string('author')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('status')->default('1'); // 0=archive, 1=active, 2=inactive
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_articles');
    }
};
