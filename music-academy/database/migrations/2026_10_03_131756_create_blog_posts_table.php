<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique()->index();
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->string('cover_image')->nullable();
            $table->string('category')->default('Classical Repertoire')->index();
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_published')->default(true)->index();
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('read_time_minutes')->default(5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
