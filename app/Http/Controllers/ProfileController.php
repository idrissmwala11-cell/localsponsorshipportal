<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['profile_photo']);

        $user = $request->user();
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('profile_photo')) {
            $disk = Storage::disk('public');
            $existingPhoto = $user->profile_photo ?: $user->getDerivedProfilePhotoPath();

            if ($existingPhoto && $disk->exists($existingPhoto)) {
                $disk->delete($existingPhoto);
            }

            $extension = $request->file('profile_photo')->getClientOriginalExtension() ?: 'jpg';
            $path = $request->file('profile_photo')->storeAs(
                'profile-photos',
                'user-' . $user->id . '.' . strtolower($extension),
                'public'
            );

            if (array_key_exists('profile_photo', $user->getAttributes()) || \Illuminate\Support\Facades\Schema::hasColumn('users', 'profile_photo')) {
                $user->profile_photo = $path;
            }
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $existingPhoto = $user->profile_photo ?: $user->getDerivedProfilePhotoPath();

        if ($existingPhoto && Storage::disk('public')->exists($existingPhoto)) {
            Storage::disk('public')->delete($existingPhoto);
        }

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
