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
        Schema::table('videos', function (Blueprint $table) {
            $table->string('title')->after('video_id'); // video_idカラムの後にtitleカラムを追加
            $table->string('thumbnail_url')->after('title'); // titleカラムの後にthumbnail_urlカラムを追加
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('thumbnail_url');
        });
    }
};
