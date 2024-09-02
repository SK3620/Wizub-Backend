<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transcript extends Model
{
    use HasFactory;

    // fillableで保存可能なプロパティを指定
    protected $fillable = ['video_id', 'transcript_id', 'en_subtitle', 'ja_subtitle', 'start', 'duration'];

    // Transcriptが属するVideoを取得するリレーション
    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
