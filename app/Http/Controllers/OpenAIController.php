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
    /* // （並行処理による字幕翻訳処理時に使用）※翻訳精度の極度な不安定性により使用中断中
    public function translateSubtitles(TranslateSubtitlesRequest $request)
    {
        // 翻訳する英語字幕を格納する配列の数
        $arrayCount = $request->array_count;

        // 翻訳する英語字幕を格納する配列
        $subtitleArray = $request->subtitles;

        // 質問内容
        $content = '';
        // 質問内容をフォーマット化
        foreach ($subtitleArray as $value) {
            $content .= "''''(ID:" . (string)$value['subtitle_id'] . ") {$value['en_subtitle']}'''\n";
        }

        // OpenAI APIキー取得
        $apiKey = config('services.open_ai.api_key');

        // Pythonスクリプトのパス
        $scriptPath = base_path('myenv/scripts/get_open_ai_answer.py');

        try {
            // 翻訳量の制限
            if ($arrayCount > 3) {
                throw new OpenAIException(message: '翻訳する字幕数が多すぎます。\n翻訳したい字幕を選択して翻訳を行なってください。', detail: 'Too Many Subtitles');
            }

            // Pythonスクリプトを実行 
            $process = new Process(['python3', $scriptPath, $apiKey, $content]);
            $process->run();

            // プロセスの成功を確認
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process); // プロセス失敗時に例外をスロー
            }

            // レスポンスを取得し、"answer"キーでラップして返す
            $response = $process->getOutput();
            return response()->json(['answer' => json_decode($response, true)]);
        } catch (OpenAIException $e) {
            Log::error('Too Many Subtitles: ' . $e->getMessage());
            throw $e;
        } catch (ProcessFailedException $e) {
            // 失敗の例外処理
            Log::error('Process Failed Exception: ' . $e->getMessage());
            throw new VideoSubtitleException(message: '字幕の翻訳に失敗しました。\n再度、お試しください。', detail: $e->getMessage());
        } catch (Exception $e) {
            // その他のエラー
            throw $e;
        }
    }
    */
}
