<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('api')->get('/get_transcript', 'App\Http\Controllers\TranscriptController@getTranscript');
    Route::middleware('api')->get('/get_open_ai_answer', 'App\Http\Controllers\OpenAIController@getOpenAIResponse');
    Route::middleware('api')->get('/search', 'App\Http\Controllers\YouTubeController@search');
});
