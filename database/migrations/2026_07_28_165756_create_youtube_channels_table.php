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
	    Schema::create('youtube_channels', function (Blueprint $table) {
	        $table->id();
	        $table->string('channel_id')->unique();
	        $table->string('channel_title');
	        $table->string('uploads_playlist_id')->nullable();
	        $table->enum('status', ['pending', 'active', 'rejected'])->default('pending');
	        $table->enum('source', ['manual', 'keyword_discovery'])->default('keyword_discovery');
	        $table->unsignedTinyInteger('health_score')->nullable();
	        $table->timestamp('last_crawled_at')->nullable();
	        $table->timestamps();
	    });
	}
	
	public function down(): void
	{
	    Schema::dropIfExists('youtube_channels');
	}
};