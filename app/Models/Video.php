<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    // fillableで保存可能なプロパティを指定
    protected $fillable = ['user_id', 'video_id', 'title', 'thumbnail_url'];

    // Videoが属するUserを取得するリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Videoに関連するTranscriptを取得するリレーション
    public function transcripts()
    {
        return $this->hasMany(Transcript::class);
    }
}
