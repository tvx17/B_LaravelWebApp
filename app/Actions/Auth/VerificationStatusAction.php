<?php

namespace App\Actions\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class VerificationStatusAction
{
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json(['verified' => $request->user()->hasVerifiedEmail()]);
    }
}
