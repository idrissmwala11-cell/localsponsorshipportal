<?php

namespace App\Providers;

use App\Models\CenterNotification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['layouts.app', 'layouts.portal-app'], function ($view) {
            $user = auth()->user();

            if (!$user || !Schema::hasTable('center_notifications')) {
                return;
            }

            $notifications = CenterNotification::query()
                ->manual()
                ->visibleToUser($user)
                ->with(['reads' => fn ($query) => $query->where('user_id', $user->id)])
                ->latest()
                ->take(5)
                ->get();

            $unreadCount = CenterNotification::query()
                ->manual()
                ->visibleToUser($user)
                ->whereDoesntHave('reads', fn ($query) => $query->where('user_id', $user->id))
                ->count();

            $view->with([
                'layoutNotifications' => $notifications,
                'layoutUnreadNotificationsCount' => $unreadCount,
            ]);
        });
    }
}
