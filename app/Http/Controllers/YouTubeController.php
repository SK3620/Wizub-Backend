<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\YouTubeService;

class YouTubeController extends Controller
{
    protected $youtubeService;

    public function __construct(YouTubeService $youtubeService)
    {
        $this->youtubeService = $youtubeService;
    }

    public function search(Request $request)
    {
        // 検索値取得
        $query = $request->query('query');
        // 追加動画を取得
        $nextPageToken = $request->query('next_page_token');

        try {
            // 検索リクエスト
            $videos = $this->youtubeService->searchVideos($query, $nextPageToken);
            return response()->json($videos);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
