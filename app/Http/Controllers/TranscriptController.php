<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TranscriptController extends Controller
{
    // 指定の動画のトランスクリプト取得
    public function getTranscript(Request $request)
    {
        $video_id = $request->query('video_id');

        $scriptPath = base_path('myenv/scripts/get_transcript.py'); // Pythonスクリプトのパス

        $process = new Process(['python3', $scriptPath, $video_id]);
        $process->run();

        // エラー処理
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $transcript = $process->getOutput();
        return response()->json(json_decode($transcript, true));
    }

    // すでに保存済みのトランスクリプトを取得
    public function getSavedTranscript(Request $request)
    {
        // Userを取得
        $user = Auth::user();

        // リクエストからvideo_idを取得
        $video_id = $request->query('video_id');

        //video_idでVideoモデルを検索し、そのトランスクリプトを取得
        $video = $user->videos()->where('video_id', $video_id)->with('transcripts')->first();

        // videoが存在しない場合、エラーレスポンスを返す
        if (!$video) {
            return response()->json(['error' => 'Video not found or not owned by the user'], 404);
        }

        // トランスクリプトのフォーマット調整
        $transcripts = $video->transcripts->map(function ($transcript) {
            return [
                'id' => $transcript->id, // モデルのプライマリーキー
                'transcript_id' => $transcript->transcript_id, // それぞれの動画のトランスクリプトのID
                'en_subtitle' => $transcript->en_subtitle ?? '', // 英語字幕
                'ja_subtitle' => $transcript->ja_subtitle ?? '', // 日本語字幕
                'start' => $transcript->start, // 字幕表示開始時間
                'duration' => $transcript->duration, // 字幕表示時間
            ];
        });

        // レスポンスを返す
        return response()->json(['transcripts' => $transcripts]);
    }

    // トランスクリプトを更新
    public function update(Request $request)
    {
        // ユーザーを取得
        $user = Auth::user();

        // リクエストのバリデーション
        $validator = Validator::make($request->all(), [
            'transcripts' => 'required|array',
            'transcripts.*.transcript_id' => 'required|integer', // それぞれの動画のトランスクリプトのID
            'transcripts.*.en_subtitle' => 'nullable|string', // 英語字幕
            'transcripts.*.ja_subtitle' => 'nullable|string', // 日本語字幕
            'transcripts.*.start' => 'required|numeric', // 字幕表示開始時間
            'transcripts.*.duration' => 'required|numeric', // 字幕表示時間
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // リクエストからvideoのidを取得
        $id = $request->query('id');

        // video_idでVideoモデルを検索し、そのトランスクリプトを取得
        $video = $user->videos()->where('id', $id)->with('transcripts')->first();

        // videoが存在しない場合、エラーレスポンスを返す
        if (!$video) {
            return response()->json(['error' => 'Video not found or not owned by the user'], 404);
        }

        // トランスクリプトを更新
        foreach ($request->transcripts as $transcriptData) {

            $transcript = $video->transcripts->firstWhere('transcript_id', $transcriptData['transcript_id']);

            $transcript->update([
                'en_subtitle' => $transcriptData['en_subtitle'] ?? '',
                'ja_subtitle' => $transcriptData['ja_subtitle'] ?? '',
                'start' => $transcriptData['start'],
                'duration' => $transcriptData['duration'],
            ]);
        }

        return response()->json(['message' => 'Transcripts updated successfully!']);
    }
}
