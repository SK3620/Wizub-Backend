<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TrialUseController extends Controller
{
    public function getTrialUserInfo(Request $request)
    {
        $trialName = config('services.trial_use.trial_name');
        $trialEmail = config('services.trial_use.trial_email');
        $trialPassword = config('services.trial_use.trial_password');

        return response()->json([
            'name' => $trialName,
            'email' => $trialEmail,
            'password' => $trialPassword,
            'is_duplicated_email' => null,
            'api_token' => ''
        ]);
    }
}
