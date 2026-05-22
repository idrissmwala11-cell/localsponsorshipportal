<x-guest-layout>
    <div class="space-y-6">
        <div class="guest-copy space-y-3">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.22em] text-blue-600">OTP Verification</p>
                <h1 class="mt-3 text-3xl font-black text-slate-900">Enter your verification code</h1>
                <p class="mt-3 max-w-lg text-sm leading-7 text-slate-600">
                    Enter the 6-digit one-time password sent to your email to complete secure access to the portal.
                </p>
            </div>
        </div>

        <div class="guest-form-card space-y-6 p-6 sm:p-8">
            <div class="rounded-3xl border border-blue-100 bg-gradient-to-r from-blue-50 to-white px-5 py-4 text-sm text-slate-700">
                <div class="flex items-start gap-3">
                    <span class="guest-field-icon"><i class="bi bi-envelope-paper-fill"></i></span>
                    <div>
                        <p class="font-extrabold text-slate-900">Verification Email</p>
                        <p class="mt-1 text-slate-600">{{ auth()->user()?->email ?? 'Your email address' }}</p>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="rounded-3xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm text-emerald-700">
                    <div class="flex items-start gap-3">
                        <span class="guest-field-icon !bg-emerald-100 !text-emerald-700"><i class="bi bi-patch-check-fill"></i></span>
                        <div>
                            <p class="font-extrabold">Success</p>
                            <p class="mt-1">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('otp.verify.submit') }}" class="space-y-6">
                @csrf

                <div class="guest-field">
                    <div class="guest-field-head">
                        <span class="guest-field-icon"><i class="bi bi-shield-lock-fill"></i></span>
                        <label for="code" class="guest-field-label">OTP Code</label>
                    </div>
                    <x-text-input
                        id="code"
                        class="mt-2 block w-full px-4 py-3 text-center text-xl tracking-[0.45em]"
                        type="text"
                        name="code"
                        maxlength="6"
                        required
                        autofocus
                        inputmode="numeric"
                        placeholder="000000"
                    />
                    <p class="mt-2 text-sm font-semibold text-slate-500">Use the 6-digit code sent within the last 5 minutes.</p>
                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                </div>

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <span class="text-sm font-semibold text-slate-500">Need a fresh code? Use resend below.</span>
                    <x-primary-button class="justify-center px-6 py-3 text-sm normal-case tracking-normal sm:min-w-[190px]">
                        <i class="bi bi-shield-check"></i>
                        <span>Verify OTP</span>
                    </x-primary-button>
                </div>
            </form>

            <form method="POST" action="{{ route('otp.resend') }}">
                @csrf
                <button type="submit" class="guest-link inline-flex items-center gap-2 text-sm text-blue-600 transition hover:text-blue-700">
                    <i class="bi bi-arrow-repeat"></i>
                    <span>Resend OTP</span>
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
