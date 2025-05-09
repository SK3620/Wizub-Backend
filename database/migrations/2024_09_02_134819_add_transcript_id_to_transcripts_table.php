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
        Schema::table('transcripts', function (Blueprint $table) {
            $table->string('transcript_id')->after('id'); // after('id'): 'id'カラムの後に追加する
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transcripts', function (Blueprint $table) {
            //
        });
    }
};
