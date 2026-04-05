<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digest_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->boolean('digest_enabled')->default(true);
            $table->string('frequency')->default('daily'); // daily | weekly
            $table->time('send_time')->default('07:00:00');
            $table->string('timezone')->default('UTC');
            $table->unsignedInteger('max_articles')->default(5);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digest_settings');
    }
};
