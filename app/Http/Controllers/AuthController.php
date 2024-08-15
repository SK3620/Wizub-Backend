<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function signUp(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required',
        ]);

        $user = User::create(['name' => $request->name, 'email' => $request->email, 'password' => Hash::make($request->password)]);

        return $user;
    }

    public function signIn(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // クエストで送られてきたパスワードをデータベースに保存されているハッシュ化されたパスワードと比較
        if (!$user || !Hash::check($request->password, $user->password)) {
            return ['error' => 'The provided credentials are incorrect'];
        }

        return ['api_token' => $user->createToken('my-token')->plainTextToken];
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