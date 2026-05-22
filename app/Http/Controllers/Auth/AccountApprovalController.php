<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountApprovalController extends Controller
{
    public function pending(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user?->isApproved() && session('otp_verified')) {
            return redirect()->route('dashboard');
        }

        return view('auth.pending-approval', [
            'user' => $user,
        ]);
    }
}
