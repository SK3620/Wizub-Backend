<?php

namespace App\Http\Controllers;

use App\Exceptions\OpenAIException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;
use App\Exceptions\VideoSubtitleException;
use Illuminate\Http\Request;
use Exception;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

class OpenAIController extends Controller
{
    public function translateSubtitles(Request $request)
    {
        // 翻訳する英語字幕を格納する配列
        $content = $request->query('content');
        Log::debug('翻訳する英文↓');
        Log::debug($content);
        // 配列の要素数
        $arrayCount = $request->query('array_count');
        $totalSubtitlesCount = $request->query('total_subtitles_count');

        // OpenAI APIキー取得
        $apiKey = config('services.open_ai.api_key');

        // Pythonスクリプトのパス
        $scriptPath = base_path('myenv/scripts/get_open_ai_answer.py');

        try {
            // 配列の要素数を用いて、翻訳量の制限を判定
            if ($arrayCount > 100) {
                throw new OpenAIException(message: '翻訳する字幕数が多すぎます。\n翻訳したい字幕を最大100個まで選択して翻訳を行なってください。', detail: 'Too Many Subtitles');
            }

            // Pythonスクリプトを実行 
            $process = new Process(['python3', $scriptPath, $apiKey, $content]);
            // タイムアウトはデフォルト値30秒→60秒へ変更
            $process->setTimeout(60);
            $process->run();

            // プロセスの成功を確認
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process); // プロセス失敗時に例外をスロー
            }

            // レスポンスを取得し、"answer"キーでラップして返す
            $response = $process->getOutput();
            Log::debug('ChatGPT翻訳結果↓');
            Log::debug($response);
            return response()->json(['answer' => json_decode($response, true)]);
        } catch (OpenAIException $e) {
            Log::error('Too Many Subtitles: ' . $e->getMessage());
            throw $e;
        } catch (ProcessTimedOutException $e) {
            Log::error('Process Timed Out Exception: ' . $e->getMessage());
            throw new OpenAIException(message: '翻訳処理がタイムアウトしました。\n再度お試し頂くか、翻訳したい字幕を最大100個まで選択して翻訳を行なってください。', detail: $e->getMessage());
        } catch (ProcessFailedException $e) {
            // 失敗の例外処理
            Log::error('Process Failed Exception: ' . $e->getMessage());
            throw new OpenAIException(message: '字幕の翻訳に失敗しました。\n再度、お試しください。', detail: $e->getMessage());
        } catch (Exception $e) {
            // その他のエラー
            throw $e;
        }
    }

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
