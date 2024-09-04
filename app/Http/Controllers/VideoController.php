<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class VideoController extends Controller
{
    // ユーザーが保存した動画を取得
    public function index()
    {
        // Userを取得
        $user = Auth::user();

        // ユーザーが保存した動画を取得し、トランスクリプトと共に取得
        $videos = $user->videos()->with('transcripts')->get();

        // レスポンスのフォーマット調整
        $response = $videos->map(function ($video) {
            return [
                'id' => $video->id, // モデルのプライマリーキー
                'video_id' => $video->video_id, // YouTube動画のID
                'title' => $video->title ?? '', // 動画のタイトル
                'thumbnail_url' => $video->thumbnail_url ?? '', // サムネイル
                'transcripts' => $video->transcripts->map(function ($transcript) {
                    return [
                        'id' => $transcript->id, // モデルのプライマリーキー
                        'transcript_id' => $transcript->transcript_id, // それぞれの動画のトランスクリプトのID
                        'en_subtitle' => $transcript->en_subtitle ?? '', // 英語字幕
                        'ja_subtitle' => $transcript->ja_subtitle ?? '', // 日本語字幕
                        'start' => $transcript->start, // 字幕表示開始時間
                        'duration' => $transcript->duration, // 字幕表示時間
                    ];
                })->toArray(),
            ];
        })->toArray();

        return response()->json(['items' => $response]);
    }

    // 保存
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video_id' => 'required', // YouTube動画のID
            'title' => 'required', // 動画のタイトル
            'thumbnail_url' => 'required', // サムネイル
            'transcripts' => 'required', // トランスクリプト
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Userを取得
        $user = Auth::user();

        // Videoを保存
        $video = $user->videos()->create([
            'video_id' => $request->video_id,
            'title' => $request->title,
            'thumbnail_url' => $request->thumbnail_url,
        ]);

        // Transcriptを保存
        foreach ($request->transcripts as $transcript) {
            $video->transcripts()->create([
                'transcript_id' => $transcript['transcript_id'],
                'en_subtitle' => $transcript['en_subtitle'] ?? '', // laravelのMiddleware\ConvertEmptyStringsToNullで空文字をnullに自動変換をするため、空文字に変換
                'ja_subtitle' => $transcript['ja_subtitle'] ?? '',
                'start' => $transcript['start'],
                'duration' => $transcript['duration'],
            ]);
        }

        return response()->json(['message' => 'Video and transcripts saved successfully!']);
    }

    // 動画が保存済みか否かチェック
    public function checkVideoAlreadySaved(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // ユーザー取得
        $user = Auth::user();

        // 動画のID
        $videoId = $request->video_id;

        // DBに同じ"videoId"が存在しているかチェック
        $video = Video::where('user_id', $user->id)
            ->where('video_id', $videoId)
            ->first();

        // 動画が見つかった場合、is_video_already_saved = true と Video の id を返す
        if ($video) {
            return response()->json([
                'is_video_already_saved' => true,
                'id' => $video->id // モデルのプライマリーキー
            ]);
        } else {
            // 見つからなかった場合は、false, null を返す
            return response()->json([
                'is_video_already_saved' => false,
                'id' => null
            ]);
        }
    }

    // 保存した動画を削除
    public function delete(Request $request)
    {
        // 削除するVideoモデルのレコードID
        $id = $request->query('id');

        // Userを取得
        $user = Auth::user();

        // 削除する動画を取得
        $video = $user->videos()->where('id', $id)->first();

        // 該当する動画が存在しない場合、エラーレスポンスを返す
        if (!$video) {
            return response()->json(['error' => 'Video not found'], 404);
        }

        // 動画とその関連するトランスクリプトを削除
        $video->transcripts()->delete(); // まず関連するトランスクリプトを削除
        $video->delete(); // その後動画を削除

        return response()->json(['message' => 'Video and its transcripts deleted successfully!']);
    }
}
