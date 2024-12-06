<?php

namespace App\Http\Controllers;

use App\Exceptions\AuthException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Exception;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function signUp(Request $request)
    {
        // アプリ側でバリデーションを実装しているためlaravel側でのバリデーションは不要

        $user = User::create(['name' => $request->name, 'email' => $request->email, 'password' => Hash::make($request->password)]);

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'is_duplicated_email' => null,
            'api_token' => $token
        ]);
    }

    public function signIn(Request $request)
    {
        // アプリ側でバリデーションを実装しているためlaravel側でのバリデーションは不要

        // usersテーブルから指定されたemailカラムの値に一致する最初のレコードを取得
        $user = User::where('email', $request->email)->first();

        // パスワードの一致をチェック
        if (!$user || !Hash::check($request->password, $user->password)) {
            // 一致しない場合は、認証エラーをHandler.phpへ投げる
            abort(Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        $trialName = config('services.trial_use.trial_name');
        $trialEmail = config('services.trial_use.trial_email');
        $trialPassword = config('services.trial_use.trial_password');
        // お試し利用中か否か
        $isTrialUse = ($request->email == $trialEmail && $request->password == $trialPassword);

        return response()->json([
            'name' => $isTrialUse ? $trialName : $request->name,
            'email' => $isTrialUse ? '' : $request->email,
            'password' => $isTrialUse ? '' : $request->password,
            'is_duplicated_email' => null,
            'api_token' => $token
        ]);
    }

    // Emailの重複チェック
    public function checkEmail(Request $request)
    {
        // アプリ側でバリデーションを実装しているためlaravel側でのバリデーションは不要

        // メールアドレスが重複しているか確認
        $emailExists = User::where('email', $request->email)->exists();

        // メールアドレスが重複しているかどうかをレスポンスで返す
        return response()->json([
            'name' => '',
            'email' => $request->email,
            'password' => '',
            'is_duplicated_email' => $emailExists,
            'api_token' => ''
        ]);
    }

    // アカウント削除
    public function deleteAccount(Request $request)
    {
        // 入力されたEメールとパスワードを取得
        $email = $request->input('email');
        $password = $request->input('password');

        try {
            // ユーザーを検索
            $user = User::where('email', $email)->first();

            if (!$user) {
                throw new AuthException(message: 'メールアドレスが正しくありません。', detail: 'Input email not found');
            }

            if (!Hash::check($password, $user->password)) {
                throw new AuthException(message: 'パスワードが正しくありません。', detail: 'Input password not correct');
            }

            $user->delete();

            return response()->json(['message' => 'アカウントが正常に削除されました'], 200);
        } catch (AuthException $e) {
            Log::error('Unauthorized: ' . $e->getMessage());
            throw $e;
        } catch (Exception $e) {
            Log::error('Failed to delete account' . $e->getMessage());
            throw new AuthException(message: 'アカウントの削除に失敗しました。', detail: 'Failed to delete account');
        }
    }
}
