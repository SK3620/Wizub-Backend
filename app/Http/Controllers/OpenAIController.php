<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class OpenAIController extends Controller
{
    public function getOpenAIResponse(Request $request)
    {
        // 質問内容 取得
        $content = $request->query('content');
        // OpenAI APIキー取得
        $apiKey = config('services.open_ai.api_key');

        // Pythonスクリプトのパス
        $scriptPath = base_path('myenv/scripts/get_open_ai_answer.py');

        // Pythonスクリプトを実行 
        $process = new Process(['python3', $scriptPath, $apiKey, $content]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // レスポンスを取得し、"answer"キーでラップして返す
        $response = $process->getOutput();
        return response()->json(['answer' => json_decode($response, true)]);
    }
}
