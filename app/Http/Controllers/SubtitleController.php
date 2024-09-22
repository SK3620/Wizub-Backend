<?php

namespace App\Http\Controllers;

use App\Exceptions\VideoSubtitleException;
use App\Http\Requests\UpdateSubtitleRequest;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;

class SubtitleController extends Controller
{
    // 指定の動画の字幕取得
    public function getSubtitles(Request $request)
    {
        $video_id = $request->query('video_id');

        // プロキシサーバーURL
        $proxy = config('services.proxy.proxy_server_url');

        // Pythonスクリプトのパス
        $scriptPath = base_path('myenv/scripts/get_transcript.py');

        // Processコンポーネントは、外部プログラムやコマンドをPHPスクリプト内で実行するためのもの
        $process = new Process(['python3', $scriptPath, $video_id, $proxy]);
        // 実行
        $process->run();

        try {
            // プロセスの成功を確認
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process); // プロセス失敗時に例外をスロー
            }

            // 以下、Googleの無料翻訳APIを使用した翻訳処理 ※翻訳精度が大きく不安定なため、使用中断中
            /* 
            // 成功した場合の処理
            $subtitles = json_decode($process->getOutput(), true); // 字幕を取得してJSONデコード

            // 翻訳処理に字幕データを渡す
            $translatedResults = $this->translateSubtitles(new Request(['subtitles' => $subtitles['subtitles']]));

            // 翻訳結果を返却
            return response()->json([
                'subtitles' => $translatedResults
            ]);
            // return response()->json($translatedResults->getData());
            */

            // 成功した場合の処理
            $subtitles = $process->getOutput();
            // フォーマット化したjsonレスポンスを返却
            return response()->json(json_decode($subtitles, true));
        } catch (ProcessFailedException $e) {
            // 失敗の例外処理
            Log::error('Process Failed Exception: ' . $e->getMessage());
            throw new VideoSubtitleException(message: '字幕の取得に失敗しました。\nこの動画には字幕が含まれていない可能性があります。', detail: $e->getMessage());
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

        try {
            // 保存した動画が存在するか
            if (!$video) {
                throw new VideoSubtitleException(message: '保存した動画が見つかりませんでした。', detail: 'Video not Found / Failed to retrive some of the saved videos');
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
        } catch (VideoSubtitleException $e) {
            throw $e;
        }
    }

    // 字幕を更新
    public function update(UpdateSubtitleRequest $request)
    {
        // ユーザーを取得
        $user = Auth::user();

        // リクエストからvideoのidを取得
        $id = $request->query('id');

        // video_idでVideoモデルを検索し、そのトランスクリプトを取得
        $video = $user->videos()->where('id', $id)->with('subtitles')->first();

        try {
            if (!$video) {
                throw new VideoSubtitleException(message: 'データの更新に失敗しました。', detail: 'Video not Found / Failed to update data');
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

    // 以下、Googleの無料翻訳APIを使用した翻訳処理 ※翻訳精度が大きく不安定なため、使用中断中
    /*
    public function translateSubtitles(Request $request)
    {
        $subtitles = $request->input('subtitles');

        // 非同期でGoogle Apps Script APIにリクエストを送る
        $client = new \GuzzleHttp\Client();
        $promises = [];

        // 各字幕に対して翻訳リクエストを送る
        foreach ($subtitles as $subtitle) {
            $subtitleId = $subtitle['subtitle_id'];
            $text = $subtitle['en_subtitle'];

            // APIのURLを構築
            $url = "https://script.google.com/macros/s/AKfycbxqTtw0kIGObLgF2LHn2a7x8Q_PIHCxZKugwNBvy-zvBYyhbrhzwhcnZ6eiae2EJcHm/exec?text={$text}&source=en&target=ja&subtitle_id={$subtitleId}";

            // 非同期リクエストを配列に保存
            $promises[] = $client->getAsync($url);
        }

        // すべての翻訳リクエストが完了するのを待つ
        $responses = Utils::settle($promises)->wait();

        // 翻訳結果を整理する
        $translatedResults = [];
        foreach ($responses as $index => $response) {
            // インデックスを利用して元の字幕データを取得
            $subtitle = $subtitles[$index];

            if ($response['state'] === 'fulfilled') {
                $body = json_decode($response['value']->getBody(), true);

                // 翻訳された結果を配列に追加
                $translatedResults[] = [
                    'id' => $subtitle['id'],
                    'subtitle_id' => $subtitle['subtitle_id'],
                    'en_subtitle' => $subtitle['en_subtitle'],
                    'ja_subtitle' => $body['text'] ?? '', // 翻訳された日本語字幕
                    'memo' => $subtitle['memo'],
                    'start' => $subtitle['start'],
                    'duration' => $subtitle['duration'],
                ];
            } else {
                // 失敗した場合も元の字幕情報を保持
                $translatedResults[] = [
                    'id' => $subtitle['id'],
                    'subtitle_id' => $subtitle['subtitle_id'],
                    'en_subtitle' => $subtitle['en_subtitle'],
                    'ja_subtitle' => '', // 翻訳失敗時は空文字
                    'memo' => $subtitle['memo'],
                    'start' => $subtitle['start'],
                    'duration' => $subtitle['duration'],
                ];
            }
        }

        // 翻訳結果を返す
        return $translatedResults;
    }
　　*/
}
