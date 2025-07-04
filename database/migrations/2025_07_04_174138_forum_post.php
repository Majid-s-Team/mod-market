<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('forum_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('description');
            $table->string('privacy')->default('public'); // public or private
            $table->boolean('is_draft')->default(false);
            $table->timestamps();
        });

        Schema::create('forum_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_post_id')->constrained()->onDelete('cascade');
            $table->string('file_path');
            $table->timestamps();
        });

        Schema::create('forum_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_post_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('forum_comments')->onDelete('cascade');
            $table->text('comment');
            $table->timestamps();
        });

        Schema::create('forum_comment_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_comment_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('reaction'); // like, love, haha, etc.
            $table->timestamps();
        });

        Schema::create('forum_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_post_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_likes');
        Schema::dropIfExists('forum_comment_reactions');
        Schema::dropIfExists('forum_comments');
        Schema::dropIfExists('forum_attachments');
        Schema::dropIfExists('forum_posts');
    }
};
