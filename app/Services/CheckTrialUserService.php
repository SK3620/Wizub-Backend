<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Exceptions\FeatureAccessDeniedInTrial;

class CheckTrialUserService
{
    // ユーザーのメールアドレスがお試し期間中のメールアドレスと一致する場合
    public static function checkTrialUser(): void
    {
        // 環境変数から取得
        $trialEmail = config('services.trial_use.trial_email');
        $user = Auth::user();

        // ユーザーのメールアドレスがお試し期間中のメールアドレスと一致する場合
        if ($user->email === $trialEmail) {
            throw new FeatureAccessDeniedInTrial(detail: "Feature Access Denied In Trial");
        }
    }
}
