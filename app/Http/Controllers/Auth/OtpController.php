<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class OtpController extends Controller
{
    public function showVerifyForm()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return view('auth.verify-otp');
    }

    public function sendOtp($user): void
    {
        // Mark previous OTPs as used
        OtpCode::where('user_id', $user->id)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        // Generate OTP
        $code = (string) random_int(100000, 999999);

        // Save OTP
        OtpCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(5),
            'is_used' => false,
        ]);

        // Professional HTML Email
        $messageBody = "
        <div style='
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            padding: 40px 20px;
        '>

            <div style='
                max-width: 600px;
                margin: auto;
                background: #ffffff;
                border-radius: 12px;
                padding: 40px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            '>

              

                <!-- TITLE -->
                <h2 style='
                    text-align: center;
                    color: #2563eb;
                    margin-bottom: 10px;
                '>
                    Local Sponsorship Portal
                </h2>

                <p style='
                    text-align: center;
                    color: #6b7280;
                    font-size: 14px;
                    margin-bottom: 30px;
                '>
                    Secure OTP Verification
                </p>

                <!-- MESSAGE -->
                <p>Hello Dear User,</p>

                <p style='
                    line-height: 1.8;
                    color: #374151;
                '>
                    Thank you for using 
                    <strong>Local Sponsorship Portal</strong>.
                    Please use the verification code below 
                    to continue accessing your account.
                    This OTP code will expire in 
                    <strong>5 minutes</strong> 
                    for security purposes.
                </p>

                <!-- OTP BOX -->
                <div style='
                    text-align:center;
                    margin:40px 0;
                '>

                    <div style='
                        display:inline-block;
                        background:#eff6ff;
                        border:2px dashed #2563eb;
                        padding:20px 40px;
                        border-radius:12px;
                        font-size:36px;
                        font-weight:bold;
                        letter-spacing:8px;
                        color:#111827;
                    '>
                        {$code}
                    </div>

                </div>

                <!-- INFO -->
                <p style='
                    line-height:1.8;
                    color:#374151;
                '>
                    If you did not request this code,
                    please ignore this email.
                </p>

                <hr style='
                    margin:30px 0;
                    border:none;
                    border-top:1px solid #e5e7eb;
                '>

                <!-- FOOTER -->
                <p style='
                    color:#6b7280;
                    line-height:1.8;
                '>
                    Best Regards,<br>

                    <strong>
                        Local Sponsorship Portal
                    </strong><br>

                    <strong>
                        System Administrator
                    </strong><br>

                    <strong>
                        Emmanuel Mwala
                    </strong><br>

                    <strong>
                        Tel: +255 673 746 031
                    </strong>
                </p>

            </div>

        </div>
        ";

        // Send Email
        Mail::html($messageBody, function ($message) use ($user) {

            $message->from(
                config('mail.from.address'),
                config('mail.from.name')
            )
            ->to($user->email)
            ->subject('Your OTP Verification Code');

        });
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $otp = OtpCode::where('user_id', Auth::id())
            ->where('code', $request->code)
            ->where('is_used', false)
            ->latest()
            ->first();

        if (!$otp) {

            return back()->withErrors([
                'code' => 'Invalid OTP code.'
            ])->withInput();

        }

        if (now()->greaterThan($otp->expires_at)) {

            return back()->withErrors([
                'code' => 'OTP has expired.'
            ])->withInput();

        }

        // Mark OTP as used
        $otp->update([
            'is_used' => true
        ]);

        // Verify session
        session([
            'otp_verified' => true
        ]);

        // Approval Check
        if (!Auth::user()?->isApproved()) {

            return redirect()
                ->route('approval.pending')
                ->with(
                    'success',
                    'OTP verified successfully. Your account is now waiting for admin approval.'
                );
        }

        // Admin Setup
        if (Auth::user()?->needsAdminOnboarding()) {

            return redirect()
                ->route('admin.setup.show')
                ->with(
                    'success',
                    'OTP verified successfully. Complete your admin setup to continue.'
                );
        }

        return redirect()
            ->route('dashboard')
            ->with(
                'success',
                'OTP verified successfully.'
            );
    }

    public function resend()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $this->sendOtp(Auth::user());

        return back()->with(
            'success',
            'A new OTP has been sent to your email.'
        );
    }
}