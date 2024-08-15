<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function signUp(Request $request)
    {
        // email重複以外のバリデーションはアプリ側で細かく実装しているため、laravel側では実際は不要
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
        ]);

        $user = User::create(['name' => $request->name, 'email' => $request->email, 'password' => Hash::make($request->password)]);

        $token = $user->createToken('api_token')->plainTextToken;

        return [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password, 
            'is_duplicated_email' => null, 
            'api_token' => $token
        ];
    }

    public function signIn(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        // usersテーブルから指定されたemailカラムの値に一致する最初のレコードを取得
        $user = User::where('email', $request->email)->first();

        // パスワードの一致をチェック
        if (!$user || !Hash::check($request->password, $user->password)) {
            return ['error' => 'The provided credentials are incorrect'];
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return [
            'name' => '',
            'email' => $request->email,
            'password' => $request->password, 
            'is_duplicated_email' => null, 
            'api_token' => $token
        ];
    }

    // Emailの重複チェック
    public function checkEmail(Request $request)
    {
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