<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class TranscriptController extends Controller
{
    public function getTranscript($video_id)
    {
        // $scriptPath = base_path('/Users/suzukikenta/Applications/MAMP/htdocs/laravel-swift-python-youtube-transcript-backend/myenv/scripts/myenv/scripts/get_transcripts.py'); // Pythonスクリプトのパス
        $scriptPath = base_path('myenv/scripts/get_transcripts.py'); // Pythonスクリプトのパス

        $process = new Process(['python3', $scriptPath, $video_id]);
        $process->run();

        // エラー処理
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $transcript = $process->getOutput();
        return response()->json(json_decode($transcript, true));
    }
}
