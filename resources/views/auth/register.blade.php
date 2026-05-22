<x-guest-layout>
    <div class="space-y-6">
        <div class="guest-copy space-y-3">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.22em] text-blue-600">Create Account</p>
                <h1 class="mt-3 text-3xl font-black text-slate-900">Register for the portal</h1>
                <p class="mt-3 max-w-lg text-sm leading-7 text-slate-600">
                    Fill in your details, enter your center manually, and add your job title so it can be displayed after you log in.
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('register') }}" class="guest-form-card space-y-7 p-6 sm:p-8">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="guest-field sm:col-span-2">
                    <div class="guest-field-head">
                        <span class="guest-field-icon"><i class="bi bi-person-badge-fill"></i></span>
                        <label class="guest-field-label">Account Type</label>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="guest-role-option">
                            <input type="radio" name="role" value="user" {{ old('role', 'user') === 'user' ? 'checked' : '' }}>
                            <span>
                                <strong>User</strong>
                                <small>Uses project name and project-specific logo.</small>
                            </span>
                        </label>
                        <label class="guest-role-option">
                            <input type="radio" name="role" value="admin" {{ old('role') === 'admin' ? 'checked' : '' }}>
                            <span>
                                <strong>Admin</strong>
                                <small>Uses the rotating portal logos after login.</small>
                            </span>
                        </label>
                        <label class="guest-role-option">
                            <input type="radio" name="role" value="official_admin" {{ old('role') === 'official_admin' ? 'checked' : '' }}>
                            <span>
                                <strong>System Administrator</strong>
                                <small>Official admin with the rotating portal logos.</small>
                            </span>
                        </label>
                    </div>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>

                <div class="guest-field sm:col-span-2">
                    <div class="guest-field-head">
                        <span class="guest-field-icon"><i class="bi bi-person-fill"></i></span>
                        <label for="name" class="guest-field-label">Full Name</label>
                    </div>
                    <x-text-input id="name" class="mt-2 block w-full px-4 py-3" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Enter your full name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="guest-field">
                    <div class="guest-field-head">
                        <span class="guest-field-icon"><i class="bi bi-briefcase-fill"></i></span>
                        <label for="job_title" class="guest-field-label">Job Title</label>
                    </div>
                    <x-text-input id="job_title" class="mt-2 block w-full px-4 py-3" type="text" name="job_title" :value="old('job_title')" required placeholder="Example: Center Coordinator" />
                    <x-input-error :messages="$errors->get('job_title')" class="mt-2" />
                </div>

                <div class="guest-field" id="project-name-field">
                    <div class="guest-field-head">
                        <span class="guest-field-icon"><i class="bi bi-building-fill"></i></span>
                        <label for="project_name" class="guest-field-label">Project Name</label>
                    </div>
                    <x-text-input id="project_name" class="mt-2 block w-full px-4 py-3" type="text" name="project_name" :value="old('project_name')" required placeholder="Example: Moravian, FPCT, TAG, EAGT" />
                    <p class="mt-2 text-xs text-slate-500">Type the project name manually and the system will detect the correct logo after login.</p>
                    <x-input-error :messages="$errors->get('project_name')" class="mt-2" />
                </div>

                <div class="guest-field" id="cluster-field">
                    <div class="guest-field-head">
                        <span class="guest-field-icon"><i class="bi bi-diagram-3-fill"></i></span>
                        <label for="cluster_name" class="guest-field-label">Cluster Name</label>
                    </div>
                    <x-text-input id="cluster_name" class="mt-2 block w-full px-4 py-3" type="text" name="cluster_name" :value="old('cluster_name')" required placeholder="Example: Kasulu Cluster" />
                    <x-input-error :messages="$errors->get('cluster_name')" class="mt-2" />
                </div>

                <div class="guest-field" id="center-field">
                    <div class="guest-field-head">
                        <span class="guest-field-icon"><i class="bi bi-geo-alt-fill"></i></span>
                        <label for="center_id" class="guest-field-label">Center</label>
                    </div>
                    <x-text-input id="center_id" class="mt-2 block w-full px-4 py-3" type="text" name="center_id" :value="old('center_id')" required placeholder="Example: TZ0342" />
                    <x-input-error :messages="$errors->get('center_id')" class="mt-2" />
                </div>

                <div class="guest-field sm:col-span-2">
                    <div class="guest-field-head">
                        <span class="guest-field-icon"><i class="bi bi-envelope-fill"></i></span>
                        <label for="email" class="guest-field-label">Email Address</label>
                    </div>
                    <x-text-input id="email" class="mt-2 block w-full px-4 py-3" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="name@example.com" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="guest-field">
                    <div class="guest-field-head">
                        <span class="guest-field-icon"><i class="bi bi-shield-lock-fill"></i></span>
                        <label for="password" class="guest-field-label">Password</label>
                    </div>
                    <x-text-input id="password" class="mt-2 block w-full px-4 py-3"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        placeholder="Create a secure password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="guest-field">
                    <div class="guest-field-head">
                        <span class="guest-field-icon"><i class="bi bi-patch-check-fill"></i></span>
                        <label for="password_confirmation" class="guest-field-label">Confirm Password</label>
                    </div>
                    <x-text-input id="password_confirmation" class="mt-2 block w-full px-4 py-3"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Repeat your password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>
            </div>

            <div class="guest-note px-5 py-4 text-sm text-slate-600">
                After registration, your account will continue to OTP verification for secure access and then wait for approval before full system use.
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <a class="guest-link text-sm text-slate-600 transition hover:text-blue-700" href="{{ route('login') }}">
                    Already registered?
                </a>

                <x-primary-button class="justify-center px-6 py-3 text-sm normal-case tracking-normal sm:min-w-[180px]">
                    Register
                </x-primary-button>
            </div>
        </form>
    </div>

    <script>
        (() => {
            const roleInputs = document.querySelectorAll('input[name="role"]');
            const projectField = document.getElementById('project-name-field');
            const projectInput = document.getElementById('project_name');
            const centerField = document.getElementById('center-field');
            const centerInput = document.getElementById('center_id');
            const clusterField = document.getElementById('cluster-field');
            const clusterInput = document.getElementById('cluster_name');

            if (!roleInputs.length || !projectField || !projectInput || !centerField || !centerInput || !clusterField || !clusterInput) {
                return;
            }

            const syncProjectField = () => {
                const selectedRole = document.querySelector('input[name="role"]:checked')?.value || 'user';
                const isStandardUser = selectedRole === 'user';

                projectField.style.display = isStandardUser ? '' : 'none';
                projectInput.required = isStandardUser;
                centerField.style.display = isStandardUser ? '' : 'none';
                centerInput.required = isStandardUser;
                clusterField.style.display = isStandardUser ? '' : 'none';
                clusterInput.required = isStandardUser;
            };

            roleInputs.forEach((input) => input.addEventListener('change', syncProjectField));
            syncProjectField();
        })();
    </script>
</x-guest-layout>
