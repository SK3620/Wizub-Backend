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
        Schema::table('subtitles', function (Blueprint $table) {
            $table->string('memo')->after('ja_subtitle'); // ja_subtitleカラムの後にtitleカラムを追加
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subtitles', function (Blueprint $table) {
            $table->dropColumn('memo');
        });
    }
};
