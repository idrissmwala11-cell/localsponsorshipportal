<x-guest-layout>
    <div class="space-y-6">
        <div class="guest-copy space-y-3">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.22em] text-blue-600">Secure Sign In</p>
                <h1 class="mt-3 text-3xl font-black text-slate-900">Log in to continue</h1>
                <p class="mt-3 max-w-lg text-sm leading-7 text-slate-600">
                    Access your center dashboard, participants, sponsorship records, and notifications through a church-led local sponsorship portal.
                </p>
            </div>
        </div>

        <div class="guest-form-card p-6 sm:p-8">
            <x-auth-session-status class="mb-4 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <div class="guest-field">
                    <div class="guest-field-head">
                        <span class="guest-field-icon"><i class="bi bi-person-badge-fill"></i></span>
                        <label class="guest-field-label">Account Type</label>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="guest-role-option">
                            <input type="radio" name="role" value="user" {{ old('role', 'user') === 'user' ? 'checked' : '' }}>
                            <span>
                                <strong>User</strong>
                                <small>Log in with your project name and project-specific logo.</small>
                            </span>
                        </label>
                        <label class="guest-role-option">
                            <input type="radio" name="role" value="admin" {{ old('role') === 'admin' ? 'checked' : '' }}>
                            <span>
                                <strong>Admin</strong>
                                <small>Project name is not required for this account.</small>
                            </span>
                        </label>
                        <label class="guest-role-option">
                            <input type="radio" name="role" value="official_admin" {{ old('role') === 'official_admin' ? 'checked' : '' }}>
                            <span>
                                <strong>System Administrator</strong>
                                <small>Project name is not required for this account.</small>
                            </span>
                        </label>
                    </div>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>

                <div class="guest-field">
                    <div class="guest-field-head">
                        <span class="guest-field-icon"><i class="bi bi-envelope-fill"></i></span>
                        <label for="email" class="guest-field-label">Email Address</label>
                    </div>
                    <x-text-input id="email" class="mt-2 block w-full px-4 py-3" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="name@example.com" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="guest-field" id="login-project-name-field">
                    <div class="guest-field-head">
                        <span class="guest-field-icon"><i class="bi bi-building-fill"></i></span>
                        <label for="project_name" class="guest-field-label">Project Name</label>
                    </div>
                    <x-text-input id="project_name" class="mt-2 block w-full px-4 py-3" type="text" name="project_name" :value="old('project_name')" placeholder="Example: Moravian, FPCT, TAG, EAGT" />
                    <p class="mt-2 text-xs text-slate-500">Required for normal user accounts. Admin and system administrator accounts can leave this field blank.</p>
                    <x-input-error :messages="$errors->get('project_name')" class="mt-2" />
                </div>

                <div class="guest-field">
                    <div class="flex items-center justify-between gap-3">
                        <div class="guest-field-head mb-0">
                            <span class="guest-field-icon"><i class="bi bi-shield-lock-fill"></i></span>
                            <label for="password" class="guest-field-label">Password</label>
                        </div>
                        @if (Route::has('password.request'))
                            <a class="guest-link text-sm text-blue-600 transition hover:text-blue-700" href="{{ route('password.request') }}">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <x-text-input id="password" class="mt-2 block w-full px-4 py-3"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Enter your password" />

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <label for="remember_me" class="guest-remember flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                    <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                    <span>Keep me signed in on this device</span>
                </label>

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    @if (Route::has('register'))
                        <a class="guest-link text-sm text-slate-600 transition hover:text-blue-700" href="{{ route('register') }}">
                            Need an account? Register
                        </a>
                    @else
                        <span></span>
                    @endif

                    <x-primary-button class="justify-center px-6 py-3 text-sm normal-case tracking-normal sm:min-w-[180px]">
                        Log In
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (() => {
            const roleInputs = document.querySelectorAll('input[name="role"]');
            const projectField = document.getElementById('login-project-name-field');
            const projectInput = document.getElementById('project_name');

            if (!roleInputs.length || !projectField || !projectInput) {
                return;
            }

            const syncProjectField = () => {
                const selectedRole = document.querySelector('input[name="role"]:checked')?.value || 'user';
                const requiresProject = selectedRole === 'user';

                projectField.style.display = requiresProject ? '' : 'none';
                projectInput.required = requiresProject;
            };

            roleInputs.forEach((input) => input.addEventListener('change', syncProjectField));
            syncProjectField();
        })();
    </script>
</x-guest-layout>
