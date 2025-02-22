<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckVideoAlreadySavedRequest;
use App\Http\Requests\StoreVideoRequest;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\VideoSubtitleException;
use Symfony\Component\HttpFoundation\Response;
use App\Services\CheckTrialUserService;

class VideoController extends Controller
{
    // ユーザーが保存した動画を取得
    public function index()
    {
        // Userを取得
        $user = Auth::user();

        // ユーザーが保存した動画を取得し、トランスクリプトと共に取得
        $videos = $user->videos()->with('subtitles')->get();

        // レスポンスのフォーマット調整
        $response = $videos->map(function ($video) {
            return [
                'id' => $video->id, // モデルのプライマリーキー
                'video_id' => $video->video_id, // YouTube動画のID
                'title' => $video->title ?? '', // 動画のタイトル
                'thumbnail_url' => $video->thumbnail_url ?? '', // サムネイル
                'is_video_already_saved' => true, // 保存した動画を取得しているためtrue
                'subtitles' => $video->subtitles->map(function ($subtitle) {
                    return [
                        'id' => $subtitle->id, // モデルのプライマリーキー
                        'subtitle_id' => $subtitle->subtitle_id, // それぞれの動画の字幕のID
                        'en_subtitle' => $subtitle->en_subtitle ?? '', // 英語字幕
                        'ja_subtitle' => $subtitle->ja_subtitle ?? '', // 日本語字幕
                        'memo' => $subtitle->memo ?? '', // 学習メモ
                        'start' => $subtitle->start, // 字幕表示開始時間
                        'duration' => $subtitle->duration, // 字幕表示時間
                    ];
                })->toArray(),
            ];
        })->toArray();

        return response()->json(['items' => $response]);
    }

    // 保存
    public function store(StoreVideoRequest $request)
    {
        // お試し利用中か否か判定
        CheckTrialUserService::checkTrialUser();

        // Userを取得
        $user = Auth::user();

        // Videoを保存
        $video = $user->videos()->create([
            'video_id' => $request->video_id,
            'title' => $request->title,
            'thumbnail_url' => $request->thumbnail_url,
        ]);

        // 字幕を保存
        foreach ($request->subtitles as $subtitle) {
            $video->subtitles()->create([
                'subtitle_id' => $subtitle['subtitle_id'],
                'en_subtitle' => $subtitle['en_subtitle'] ?? '', // laravelのMiddleware\ConvertEmptyStringsToNullで空文字をnullに自動変換をするため、空文字に変換
                'ja_subtitle' => $subtitle['ja_subtitle'] ?? '',
                'memo' => $subtitle['memo'] ?? '',
                'start' => $subtitle['start'],
                'duration' => $subtitle['duration'],
            ]);
        }

        // レスポンスマクロ
        return response()->success(Response::HTTP_OK, 'Subtitles Saved Successfully');
    }

    // 動画が保存済みか否かチェック
    public function checkVideoAlreadySaved(CheckVideoAlreadySavedRequest $request)
    {
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
    public function delete($id)
    {
        // 削除するVideoモデルのレコードID
        // $id = $request->query('id');

        // $idをintにキャスト
        $id = (int) $id;

        // Userを取得
        $user = Auth::user();

        // 削除する動画を取得
        $video = $user->videos()->where('id', $id)->first();

        try {
            // 該当する動画が存在しない場合、エラーレスポンスを返す
            if ($video == null) {
                throw new VideoSubtitleException(message: '動画の削除に失敗しました。', detail: 'Video not Found / Failed to delete data');
            }

            // 動画とその関連するトランスクリプトを削除
            $video->subtitles()->delete(); // まず関連するトランスクリプトを削除
            $video->delete(); // その後動画を削除

            // レスポンスマクロ
            return response()->success(Response::HTTP_OK, 'Video Deleted Successfully');
        } catch (VideoSubtitleException $e) {
            throw $e;
        }
    }
}
