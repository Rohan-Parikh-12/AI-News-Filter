<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->unique()->constrained('news_articles')->cascadeOnDelete();
            $table->text('summary');
            $table->string('ai_model')->default('gemini-2.0-flash');
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('completion_tokens')->default(0);
            $table->decimal('relevance_score', 3, 2)->nullable(); // 0.00 - 1.00
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_summaries');
    }
};
