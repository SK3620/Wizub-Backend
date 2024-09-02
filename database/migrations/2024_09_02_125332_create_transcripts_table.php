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
        Schema::create('transcripts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained()->onDelete('cascade'); // 動画IDと関連付ける
            $table->text('en_subtitle'); // 英語字幕
            $table->text('ja_subtitle'); // 日本語字幕
            $table->double('start'); // 字幕表示開始時間
            $table->double('duration'); // 字幕が表示されている時間の長さ
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcripts');
    }
};
