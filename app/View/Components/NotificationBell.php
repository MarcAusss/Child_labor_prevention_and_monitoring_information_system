<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class NotificationBell extends Component
{
    public function render(): View
    {
        $user = Auth::user();

        $unreadCount = $user
            ? $user
                ->unreadNotifications()
                ->count()
            : 0;

        $latestNotifications = $user
            ? $user
                ->notifications()
                ->latest()
                ->limit(5)
                ->get()
            : collect();

        return view(
            'components.notification-bell',
            compact(
                'unreadCount',
                'latestNotifications'
            )
        );
    }
}
