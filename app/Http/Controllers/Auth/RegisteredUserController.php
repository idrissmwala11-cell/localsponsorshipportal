<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Throwable;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'job_title' => ['required', 'string', 'max:255'],
                'role' => ['required', Rule::in([
                    User::ROLE_USER,
                    User::ROLE_ADMIN,
                    User::ROLE_OFFICIAL_ADMIN,
                ])],
                'project_name' => ['nullable', 'string', 'max:255'],
                'center_id' => ['nullable', 'string', 'max:255'],
                'cluster_name' => ['nullable', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            $selectedRole = (string) $request->input('role', User::ROLE_USER);
            $projectName = $selectedRole === User::ROLE_USER
                ? trim((string) $request->project_name)
                : User::defaultPortalTitle();
            $isOfficialAdmin = $selectedRole === User::ROLE_OFFICIAL_ADMIN;
            $centerId = $selectedRole === User::ROLE_USER
                ? trim((string) $request->center_id)
                : null;
            $clusterName = $selectedRole === User::ROLE_USER
                ? trim((string) $request->cluster_name)
                : null;

            if ($selectedRole === User::ROLE_USER && $projectName === '') {
                return back()
                    ->withErrors(['project_name' => 'Project name is required for user accounts.'])
                    ->withInput();
            }

            if ($selectedRole === User::ROLE_USER && $centerId === '') {
                return back()
                    ->withErrors(['center_id' => 'Center is required for user accounts.'])
                    ->withInput();
            }

            if ($selectedRole === User::ROLE_USER && $clusterName === '') {
                return back()
                    ->withErrors(['cluster_name' => 'Cluster is required for user accounts.'])
                    ->withInput();
            }

            $attributes = [
                'name' => $request->name,
                'center_id' => $centerId,
                'cluster_name' => $clusterName,
                'role' => $selectedRole,
                'project_name' => $projectName,
                'email' => $request->email,
                'approved_at' => $isOfficialAdmin ? now() : null,
                'approved_by' => null,
                'password' => Hash::make($request->password),
            ];

            if (Schema::hasColumn('users', 'job_title')) {
                $attributes['job_title'] = $request->job_title;
            }

            $user = User::create($attributes);

            event(new Registered($user));

            Auth::login($user);

            session()->forget('otp_verified');

            return redirect()->route('otp.verify');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::error('User registration failed.', [
                'email' => $request->input('email'),
                'message' => $exception->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Registration could not be completed. Please review the form and try again.');
        }
    }
}
