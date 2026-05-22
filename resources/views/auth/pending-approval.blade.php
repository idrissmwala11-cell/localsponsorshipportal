<x-guest-layout>
    <div class="space-y-6">
        <div class="guest-copy space-y-3">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.22em] text-amber-600">Account Approval</p>
                <h1 class="mt-3 text-3xl font-black text-slate-900">Your account is waiting for admin approval</h1>
                <p class="mt-3 max-w-lg text-sm leading-7 text-slate-600">
                    You have signed in successfully, but you cannot use the system until an admin approves your account.
                    After approval, refresh this page to continue.
                </p>
            </div>
        </div>

        <div class="guest-form-card space-y-6 p-6 sm:p-8">
            @if (session('success'))
                <div class="rounded-3xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-3xl border border-amber-100 bg-amber-50 px-5 py-4 text-sm text-amber-700">
                    {{ session('error') }}
                </div>
            @endif

            <div class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm text-slate-700">
                <p class="font-extrabold text-slate-900">Signed in as</p>
                <p class="mt-1">{{ $user?->name }}</p>
                <p class="text-slate-500">{{ $user?->email }}</p>
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('approval.pending') }}" class="guest-link inline-flex items-center gap-2 text-sm text-blue-600 transition hover:text-blue-700">
                    <i class="bi bi-arrow-clockwise"></i>
                    <span>Refresh page</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-primary-button class="justify-center px-6 py-3 text-sm normal-case tracking-normal">
                        Sign Out
                    </x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
