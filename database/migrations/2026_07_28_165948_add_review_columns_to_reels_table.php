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
	    Schema::table('reels', function (Blueprint $table) {
	        $table->enum('review_status', ['auto_approved', 'pending_review', 'rejected'])->default('auto_approved');
	        $table->enum('found_via', ['keyword_search', 'channel_crawl', 'manual'])->default('manual');
	        $table->foreignId('youtube_channel_id')->nullable()->constrained('youtube_channels')->nullOnDelete();
	    });
	}
	
	public function down(): void
	{
	    Schema::table('reels', function (Blueprint $table) {
	        $table->dropForeign(['youtube_channel_id']);
	        $table->dropColumn(['review_status', 'found_via', 'youtube_channel_id']);
	    });
	}
};