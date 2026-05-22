<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class AdminSetupController extends Controller
{
    public function show(Request $request): View
    {
        $admin = $request->user();
        $clusterSummaries = User::query()
            ->select('cluster_name')
            ->where('role', User::ROLE_USER)
            ->whereNotNull('cluster_name')
            ->where('cluster_name', '!=', '')
            ->whereNotNull('center_id')
            ->where('center_id', '!=', '')
            ->distinct()
            ->orderBy('cluster_name')
            ->get();
        $selectedClusters = $admin->managedClusterAssignments()
            ->pluck('cluster_name')
            ->filter()
            ->unique()
            ->values()
            ->all();

        return view('admin.setup', [
            'clusterSummaries' => $clusterSummaries->map(function ($user) {
                $clusterName = $user->cluster_name;
                $clusterUsers = User::query()
                    ->where('role', User::ROLE_USER)
                    ->where('cluster_name', $clusterName)
                    ->whereNotNull('center_id')
                    ->where('center_id', '!=', '')
                    ->get();

                return [
                    'cluster_name' => $clusterName,
                    'centers_count' => $clusterUsers->pluck('center_id')->filter()->unique()->count(),
                    'users_count' => $clusterUsers->count(),
                ];
            }),
            'selectedClusters' => $selectedClusters,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $admin = $request->user();
            $data = $request->validate([
                'managed_cluster_names' => ['required', 'array', 'min:1'],
                'managed_cluster_names.*' => ['string', 'max:255'],
            ]);

            $selectedClusters = collect($data['managed_cluster_names'])
                ->map(fn ($name) => trim((string) $name))
                ->filter()
                ->unique()
                ->values();

            $existingClusters = User::query()
                ->where('role', User::ROLE_USER)
                ->whereIn('cluster_name', $selectedClusters)
                ->pluck('cluster_name')
                ->filter()
                ->unique()
                ->values();

            if ($selectedClusters->diff($existingClusters)->isNotEmpty()) {
                return back()
                    ->withInput()
                    ->withErrors(['managed_cluster_names' => 'One or more selected clusters are invalid.']);
            }

            $managedUsers = User::query()
                ->where('role', User::ROLE_USER)
                ->whereIn('cluster_name', $selectedClusters)
                ->whereNotNull('center_id')
                ->where('center_id', '!=', '')
                ->orderBy('center_id')
                ->get();

            $managedCenterIds = $managedUsers->pluck('center_id')->filter()->unique()->values()->all();
            $supervisedUserIds = $managedUsers->pluck('id')->all();

            $admin->forceFill([
                'center_id' => $managedCenterIds[0] ?? $admin->center_id,
                'admin_onboarded_at' => now(),
            ])->save();

            if (Schema::hasTable('center_user_assignments')) {
                $admin->managedCenters()->sync($managedCenterIds);
            }
            $admin->managedClusterAssignments()->delete();
            $admin->managedClusterAssignments()->createMany(
                $selectedClusters->map(fn ($clusterName) => ['cluster_name' => $clusterName])->all()
            );
            $admin->supervisedUsers()->sync($supervisedUserIds);

            return redirect()
                ->route('admin.index')
                ->with('success', 'Admin setup saved successfully. You can now monitor all users registered in your selected clusters.');
        } catch (Throwable $exception) {
            Log::error('Admin setup save failed.', [
                'admin_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Admin setup could not be saved. Please review the form and try again.');
        }
    }
}
