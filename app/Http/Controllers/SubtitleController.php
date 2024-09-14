<?php

namespace App\Http\Controllers;

use App\Exceptions\VideoSubtitleException;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Log;

class SubtitleController extends Controller
{
    // 指定の動画の字幕取得
    public function getSubtitles(Request $request)
    {
        $video_id = $request->query('video_id');

        // Pythonスクリプトのパス
        $scriptPath = base_path('myenv/scripts/get_transcript.py');

        // Processコンポーネントは、外部プログラムやコマンドをPHPスクリプト内で実行するためのもの
        $process = new Process(['python3', $scriptPath, $video_id]);
        // 実行
        $process->run();

        try {
            // プロセスの成功を確認
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process); // プロセス失敗時に例外をスロー
            }

            // 成功した場合の処理
            $subtitles = $process->getOutput();
            // フォーマット化したjsonレスポンスを返却
            return response()->json(json_decode($subtitles, true));
        } catch (ProcessFailedException $e) {
            // 失敗の例外処理
            Log::error('Process Failed Exception: ' . $e->getMessage());
            throw new VideoSubtitleException(detail: $e->getMessage());
        } catch (Exception $e) {
            // その他のエラー
            throw $e;
        }
    }

    // すでに保存済みの字幕を取得
    public function getSavedSubtitles(Request $request)
    {
        // Userを取得
        $user = Auth::user();

        // リクエストからvideo_idを取得
        $video_id = $request->query('video_id');

        //video_idでVideoモデルを検索し、その字幕を取得
        $video = $user->videos()->where('video_id', $video_id)->with('subtitles')->first();

        // videoが存在しない場合、エラーレスポンスを返す
        if (!$video) {
            return response()->json(['error' => 'Video not found or not owned by the user'], 404);
        }

        // 字幕のフォーマット調整
        $subtitles = $video->subtitles->map(function ($subtitle) {
            return [
                'id' => $subtitle->id, // モデルのプライマリーキー
                'subtitle_id' => $subtitle->subtitle_id, // それぞれの動画のトランスクリプトのID
                'en_subtitle' => $subtitle->en_subtitle ?? '', // 英語字幕
                'ja_subtitle' => $subtitle->ja_subtitle ?? '', // 日本語字幕
                'memo' => $subtitle->memo ?? '', // 学習メモ
                'start' => $subtitle->start, // 字幕表示開始時間
                'duration' => $subtitle->duration, // 字幕表示時間
            ];
        });

        // レスポンスを返す
        return response()->json(['subtitles' => $subtitles]);
    }

    // 字幕を更新
    public function update(Request $request)
    {
        // ユーザーを取得
        $user = Auth::user();

        // リクエストのバリデーション
        $validator = Validator::make($request->all(), [
            'subtitles' => 'required|array',
            'subtitles.*.subtitle_id' => 'required|integer', // それぞれの動画の字幕のID
            'subtitles.*.en_subtitle' => 'nullable|string', // 英語字幕
            'subtitles.*.ja_subtitle' => 'nullable|string', // 日本語字幕
            'subtitles.*.memo' => 'nullable|string', // 学習メモ
            'subtitles.*.start' => 'required|numeric', // 字幕表示開始時間
            'subtitles.*.duration' => 'required|numeric', // 字幕表示時間
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // リクエストからvideoのidを取得
        $id = $request->query('id');

        // video_idでVideoモデルを検索し、そのトランスクリプトを取得
        $video = $user->videos()->where('id', $id)->with('subtitles')->first();

        // videoが存在しない場合、エラーレスポンスを返す
        if (!$video) {
            return response()->json(['error' => 'Video not found or not owned by the user'], 404);
        }

        // トランスクリプトを更新
        foreach ($request->subtitles as $subtitleData) {

            $subtitle = $video->subtitles->firstWhere('subtitle_id', $subtitleData['subtitle_id']);

            $subtitle->update([
                'en_subtitle' => $subtitleData['en_subtitle'] ?? '',
                'ja_subtitle' => $subtitleData['ja_subtitle'] ?? '',
                'memo' => $subtitleData['memo'] ?? '',
                'start' => $subtitleData['start'],
                'duration' => $subtitleData['duration'],
            ]);
        }

            // レスポンスマクロ
            return response()->success(Response::HTTP_OK, 'Subtitles Updated Successfully');
        } catch (VideoSubtitleException $e) {
            throw $e;
        }
    }
}
