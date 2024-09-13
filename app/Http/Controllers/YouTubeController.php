<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\YouTubeService;
use App\Exceptions\Handler;
use Exception;
use Google_Exception;
use Google_Service_Exception;

class YouTubeController extends Controller
{
    protected $youtubeService;

    // 依存関係を注入 
    public function __construct(YouTubeService $youtubeService)
    {
        $this->youtubeService = $youtubeService;
    }

    public function search(Request $request)
    {
        // 特にバリデーションは不要

        // 検索値取得
        $query = $request->query('query');

        try {
            // 検索リクエスト
            $videos = $this->youtubeService->searchVideos($query);
            return response()->json($videos);
        } catch (Google_Service_Exception $e) {
            // Google API 特有のエラー処理
            throw $e;
        } catch (Google_Exception $e) {
            // Google Clientのエラー処理
            throw $e;
        } catch (Exception $e) {
            // その他のエラー
            throw $e;
        }
    }
}
