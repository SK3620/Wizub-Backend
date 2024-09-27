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

Route::middleware('api')->post('/sign_up', 'App\Http\Controllers\AuthController@signUp');
Route::middleware('api')->post('/sign_in', 'App\Http\Controllers\AuthController@signIn');
Route::middleware('api')->post('/check_email', 'App\Http\Controllers\AuthController@checkEmail');
Route::middleware('api')->delete('/delete_account', 'App\Http\Controllers\AuthController@deleteAccount');

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
