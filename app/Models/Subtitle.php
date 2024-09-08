<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subtitle extends Model
{
    use HasFactory;

    // fillableで保存可能なプロパティを指定
    protected $fillable = ['video_id', 'subtitle_id', 'en_subtitle', 'ja_subtitle', 'start', 'duration'];

    // Subtitleが属するVideoを取得するリレーション
    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
