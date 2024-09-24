<?php

namespace App\Services;

use Google_Client;
use Google_Service_YouTube;
use Google_Service_Exception;
use Google_Exception;
use Illuminate\Support\Facades\Auth;
use App\Models\Video;
use Exception;
use Illuminate\Support\Facades\Log;

class YouTubeService
{
    protected $client;
    protected $youtube;

    // 動画の最大取得件数
    protected $maxResutls = 30;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setDeveloperKey(config('services.you_tube.api_key'));
        $this->youtube = new Google_Service_YouTube($this->client);
    }

    public function searchVideos($query)
    {
        $parameters = [
            'q' => $query, // 検索値
            'type' => 'video',
            'maxResults' => $this->maxResutls, // 動画の最大取得件数
        ];

        try {
            $searchResponse = $this->youtube->search->listSearch('snippet', $parameters);

            // レスポンスフォーマットの調整
            $videos = array_map(function ($item) {

                // Userを取得
                $user = Auth::user();

                // デフォルト値を設定
                $isVideoAlreadySaved = false;
                $id = null;

                // DBに同じ"videoId"が存在しているかチェック
                $video = Video::where('user_id', $user->id)
                    ->where('video_id', $item['id']['videoId'])
                    ->first();

                // 存在している場合
                if ($video) {
                    $isVideoAlreadySaved = true;
                    // アプリ側に"id"も返す
                    $id = $video->id;
                }

                return [
                    'id' => $id,
                    'video_id' => $item['id']['videoId'],
                    'title' => $item['snippet']['title'],
                    'thumbnail_url' => $item['snippet']['thumbnails']['medium']['url'],
                    'is_video_already_saved' => $isVideoAlreadySaved,
                    'subtitles' => []
                ];
            }, $searchResponse['items']);

            // レスポンスのフォーマット調整
            return [
                'items' => $videos
            ];
        } catch (Google_Service_Exception $e) {
            // Google API 特有のエラー処理
            Log::error('Google Service Exception: ' . $e->getMessage());
            throw $e;
        } catch (Google_Exception $e) {
            // Google Clientのエラー処理
            Log::error('Google Exception: ' . $e->getMessage());
            throw $e;
        } catch (Exception $e) {
            // その他のエラー
            Log::error('Unexpected Exception: ' . $e->getMessage());
            throw $e;
        }
    }
}
