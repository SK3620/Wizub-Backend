<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// お試し利用用のユーザーを取得
Route::middleware('api')->get('/get_trial_user_info', 'App\Http\Controllers\TrialUseController@getTrialUserInfo');

// 認証
Route::middleware('api')->post('/auth/sign_up', 'App\Http\Controllers\AuthController@signUp');
Route::middleware('api')->post('/auth/sign_in', 'App\Http\Controllers\AuthController@signIn');
Route::middleware('api')->post('/auth/check_email', 'App\Http\Controllers\AuthController@checkEmail');
Route::middleware('api')->delete('/auth/delete_account', 'App\Http\Controllers\AuthController@deleteAccount');

Route::middleware('auth:sanctum')->group(function () {
    // YouTube動画検索
    Route::middleware('api')->get('/videos/search', 'App\Http\Controllers\YouTubeController@search');
    
    // 字幕を取得
    Route::middleware('api')->get('/subtitles/{video_id}', 'App\Http\Controllers\SubtitleController@getSubtitles');
    
    // 字幕を更新
    Route::middleware('api')->put('/subtitles/{id}', 'App\Http\Controllers\SubtitleController@update');
    
    // 保存された字幕を取得
    // "subtitles{video_id}/saved"の方が望ましい
    Route::middleware('api')->get('/subtitles/saved/{video_id}', 'App\Http\Controllers\SubtitleController@getSavedSubtitles');
    
    // 字幕を翻訳
    Route::middleware('api')->post('/subtitles/translate', 'App\Http\Controllers\OpenAIController@translateSubtitles');
    
    // 動画情報と字幕を保存
    Route::middleware('api')->post('/subtitles', 'App\Http\Controllers\VideoController@store');
    
    // 保存された動画を取得
    Route::middleware('api')->get('/videos/saved', 'App\Http\Controllers\VideoController@index');
    
    // 保存された動画を削除
    Route::middleware('api')->delete('/videos/{id}', 'App\Http\Controllers\VideoController@delete');
    
    // 動画がすでに保存されているか確認
    Route::middleware('api')->post('/videos/check', 'App\Http\Controllers\VideoController@checkVideoAlreadySaved');
});

/*
Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('api')->get('/search', 'App\Http\Controllers\YouTubeController@search');
    Route::middleware('api')->get('/get_subtitles', 'App\Http\Controllers\SubtitleController@getSubtitles');
    Route::middleware('api')->put('/update_subtitles', 'App\Http\Controllers\SubtitleController@update');
    Route::middleware('api')->get('/get_saved_subtitles', 'App\Http\Controllers\SubtitleController@getSavedSubtitles');
    Route::middleware('api')->get('/translate_subtitles', 'App\Http\Controllers\OpenAIController@translateSubtitles');
    Route::middleware('api')->post('/store_subtitles', 'App\Http\Controllers\VideoController@store');
    Route::middleware('api')->get('/get_saved_videos', 'App\Http\Controllers\VideoController@index');
    Route::middleware('api')->delete('/delete_saved_videos', 'App\Http\Controllers\VideoController@delete');
    Route::middleware('api')->post('/check_video_already_saved', 'App\Http\Controllers\VideoController@checkVideoAlreadySaved');
});
*/
