<?php

namespace App\Services;

use Google_Client;
use Google_Service_YouTube;
use Google_Service_Exception;
use Google_Exception;
use Illuminate\Support\Facades\Auth;
use App\Models\Video;

class YouTubeService
{
    protected $client;
    protected $youtube;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setDeveloperKey(config('services.you_tube.api_key'));
        $this->youtube = new Google_Service_YouTube($this->client);
    }

    public function searchVideos($query, $nextPageToken = null) // 初期検索時はデフォルトでnull
    {
        $parameters = [
            'q' => $query, // 検索値
            'type' => 'video',
            'maxResults' => 10, // 最大10件取得
        ];

        // 追加ページ取得 nullでない場合にのみパラメータに追加
        if ($nextPageToken) {
            $parameters['pageToken'] = $nextPageToken;
        }

        try {
            $searchResponse = $this->youtube->search->listSearch('snippet', $parameters);

            // レスポンスフォーマットの調整
            $videos = array_map(function ($item) {

                // Userを取得
                $user = Auth::user();

                // DBに同じ"videoId"が存在しているかチェック
                $isVideoAlreadySaved = Video::where('user_id', $user->id)
                    ->where('video_id', $item['id']['videoId'])
                    ->exists();

                return [
                    'video_id' => $item['id']['videoId'],
                    'title' => $item['snippet']['title'],
                    'thumbnail_url' => $item['snippet']['thumbnails']['default']['url'],
                    'is_video_already_saved' => $isVideoAlreadySaved,
                ];
            }, $searchResponse['items']);

            // アプリ側のフォーマットに合わせたレスポンスを返す
            return [
                'items' => $videos,
                'next_page_token' => $searchResponse['nextPageToken'] ?? '',
            ];
        } catch (Google_Service_Exception $e) {
            throw new \Exception("Google API service error: " . $e->getMessage());
        } catch (Google_Exception $e) {
            throw new \Exception("Google client error: " . $e->getMessage());
        }
    }
}
