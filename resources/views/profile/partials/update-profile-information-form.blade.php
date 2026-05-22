<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form id="profile-information-form" method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="rounded-3xl border border-slate-200 bg-slate-50/80 p-5">
            <x-input-label for="profile_photo" :value="__('Profile Picture')" />
            <div class="mt-4 flex flex-col sm:flex-row sm:items-center gap-4">
                @if ($user->profile_photo_url)
                    <img id="profile-photo-preview" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="h-20 w-20 rounded-3xl object-cover border border-slate-200">
                @else
                    <div id="profile-photo-fallback" class="h-20 w-20 rounded-3xl bg-blue-100 border border-blue-200 flex items-center justify-center text-blue-700 text-xl font-bold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <img id="profile-photo-preview" src="" alt="{{ $user->name }}" class="hidden h-20 w-20 rounded-3xl object-cover border border-slate-200">
                @endif

                <div class="flex-1">
                    <label for="profile_photo" class="inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-3 border border-slate-200 text-sm font-semibold text-slate-700 cursor-pointer hover:border-blue-300 hover:bg-blue-50 transition">
                        <i class="bi bi-image"></i>
                        <span>Choose Picture</span>
                    </label>
                    <input id="profile_photo" name="profile_photo" type="file" class="hidden" accept=".jpg,.jpeg,.png,.webp,image/*" />
                    <p class="mt-3 text-sm text-slate-500">When you choose a picture, it will be saved automatically.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
                </div>
            </div>
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full profile-field" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full profile-field" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="account_type" :value="__('Account Type')" />
            <x-text-input
                id="account_type"
                type="text"
                class="mt-1 block w-full profile-field bg-slate-50"
                :value="\App\Models\User::roles()[$user->role] ?? ucfirst((string) $user->role)"
                readonly />
            <p class="mt-2 text-sm text-slate-500">This is the role you selected when the account was registered.</p>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>

    <script>
        const profilePhotoInput = document.getElementById('profile_photo');
        const profileInformationForm = document.getElementById('profile-information-form');
        const profilePhotoPreview = document.getElementById('profile-photo-preview');
        const profilePhotoFallback = document.getElementById('profile-photo-fallback');

        if (profilePhotoInput && profileInformationForm) {
            profilePhotoInput.addEventListener('change', function (event) {
                const file = event.target.files[0];

                if (!file) {
                    return;
                }

                if (profilePhotoPreview) {
                    profilePhotoPreview.src = URL.createObjectURL(file);
                    profilePhotoPreview.classList.remove('hidden');
                }

                if (profilePhotoFallback) {
                    profilePhotoFallback.classList.add('hidden');
                }

                profileInformationForm.submit();
            });
        }
    </script>
</section>
