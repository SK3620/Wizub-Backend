<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

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
            return ['error' => 'The provided credentials are incorrect'];
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'name' => '',
            'email' => $request->email,
            'password' => $request->password,
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
}
