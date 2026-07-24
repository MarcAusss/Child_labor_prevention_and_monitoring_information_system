<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class NotificationController extends Controller
{
    public function __construct(
        private readonly ActivityLogger
            $activityLogger
    ) {
    }

    public function index(Request $request): View
    {
        $state = trim(
            (string) $request->query(
                'state',
                'all'
            )
        );

        if (! in_array(
            $state,
            [
                'all',
                'unread',
                'read',
            ],
            true
        )) {
            $state = 'all';
        }

        $type = trim(
            (string) $request->query(
                'type',
                ''
            )
        );

        if (! array_key_exists(
            $type,
            NotificationService::typeOptions()
        )) {
            $type = '';
        }

        $search = trim(
            (string) $request->query(
                'search',
                ''
            )
        );

        $user = $request->user();

        $notifications = $user
            ->notifications()
            ->when(
                $state === 'unread',
                fn ($query) =>
                    $query->whereNull(
                        'read_at'
                    )
            )
            ->when(
                $state === 'read',
                fn ($query) =>
                    $query->whereNotNull(
                        'read_at'
                    )
            )
            ->when(
                $type !== '',
                fn ($query) =>
                    $query->where(
                        'data->notification_type',
                        $type
                    )
            )
            ->when(
                $search !== '',
                function ($query) use (
                    $search
                ): void {
                    $query->where(
                        function ($query) use (
                            $search
                        ): void {
                            $query
                                ->where(
                                    'data->title',
                                    'like',
                                    '%'.$search.'%'
                                )
                                ->orWhere(
                                    'data->message',
                                    'like',
                                    '%'.$search.'%'
                                )
                                ->orWhere(
                                    'data->actor_name',
                                    'like',
                                    '%'.$search.'%'
                                );
                        }
                    );
                }
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view(
            'notifications.index',
            [
                'notifications' =>
                    $notifications,

                'unreadCount' =>
                    $user
                        ->unreadNotifications()
                        ->count(),

                'readCount' =>
                    $user
                        ->readNotifications()
                        ->count(),

                'totalCount' =>
                    $user
                        ->notifications()
                        ->count(),

                'state' =>
                    $state,

                'selectedType' =>
                    $type,

                'search' =>
                    $search,

                'typeOptions' =>
                    NotificationService::typeOptions(),
            ]
        );
    }

    public function open(
        Request $request,
        string $notification
    ): RedirectResponse {
        $record = $this->findForUser(
            $request,
            $notification
        );

        if (! $record->read_at) {
            $record->markAsRead();
        }

        $this->activityLogger->log(
            action: 'notification_opened',
            description:
                'Opened notification: '
                .data_get(
                    $record->data,
                    'title',
                    'System notification'
                ),
            metadata: [
                'notification_id' =>
                    $record->id,

                'notification_type' =>
                    data_get(
                        $record->data,
                        'notification_type'
                    ),
            ],
            actor: $request->user(),
            childLaborerId:
                data_get(
                    $record->data,
                    'child_laborer_id'
                )
        );

        return $this->targetRedirect(
            $record
        );
    }

    public function markRead(
        Request $request,
        string $notification
    ): RedirectResponse {
        $record = $this->findForUser(
            $request,
            $notification
        );

        $record->markAsRead();

        return back()->with(
            'success',
            'The notification was marked as read.'
        );
    }

    public function markUnread(
        Request $request,
        string $notification
    ): RedirectResponse {
        $record = $this->findForUser(
            $request,
            $notification
        );

        $record->markAsUnread();

        return back()->with(
            'success',
            'The notification was marked as unread.'
        );
    }

    public function markAllRead(
        Request $request
    ): RedirectResponse {
        $count = $request
            ->user()
            ->unreadNotifications()
            ->count();

        $request
            ->user()
            ->unreadNotifications
            ->markAsRead();

        $this->activityLogger->log(
            action: 'notifications_marked_read',
            description:
                'Marked all notifications as read.',
            metadata: [
                'notification_count' =>
                    $count,
            ],
            actor: $request->user()
        );

        return back()->with(
            'success',
            $count > 0
                ? number_format($count)
                    .' notification(s) were marked as read.'
                : 'There were no unread notifications.'
        );
    }

    private function findForUser(
        Request $request,
        string $notification
    ): DatabaseNotification {
        return $request
            ->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();
    }

    private function targetRedirect(
        DatabaseNotification $notification
    ): RedirectResponse {
        $routeName = data_get(
            $notification->data,
            'route_name'
        );

        $parameters = data_get(
            $notification->data,
            'route_parameters',
            []
        );

        if (
            ! is_string($routeName)
            || $routeName === ''
            || ! Route::has($routeName)
        ) {
            return redirect()
                ->route('notifications.index');
        }

        if (! is_array($parameters)) {
            $parameters = [];
        }

        try {
            return redirect()
                ->route(
                    $routeName,
                    $parameters
                );
        } catch (
            RouteNotFoundException
            | \InvalidArgumentException
        ) {
            return redirect()
                ->route('notifications.index');
        }
    }
}
