<x-app-layout>
    <x-slot name="header">Profile Settings</x-slot>

    <div class="workspace-page">
        <div class="workspace-narrow space-y-6">
            <div class="workspace-hero px-6 md:px-8 py-6">
                <p class="workspace-label">Account</p>
                <h1 class="text-2xl md:text-3xl font-bold text-slate-900 mt-2">Profile Settings</h1>
                <p class="text-slate-500 mt-2 text-sm md:text-base">
                    Update your profile information, upload your picture, change password, or manage your account.
                </p>
            </div>

            <div class="workspace-panel p-4 sm:p-8">
                <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="workspace-panel p-4 sm:p-8">
                <div class="max-w-2xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="workspace-panel p-4 sm:p-8">
                <div class="max-w-2xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
