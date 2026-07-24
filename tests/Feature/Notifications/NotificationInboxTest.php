<?php

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationInboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_notification_inbox(): void
    {
        $this->get(
            route('notifications.index')
        )->assertRedirect(
            route('login')
        );
    }

    public function test_authenticated_user_can_open_notification_inbox(): void
    {
        $user = User::factory()->create();

        $user->notify(
            new SystemNotification(
                title: 'Test Notification',
                message: 'This is a test notification.'
            )
        );

        $this->actingAs($user)
            ->get(
                route('notifications.index')
            )
            ->assertOk()
            ->assertSee('Test Notification');
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $owner = User::factory()->create();

        $otherUser = User::factory()->create();

        $owner->notify(
            new SystemNotification(
                title: 'Private Notification',
                message: 'Only the owner may access this.'
            )
        );

        $notification = $owner
            ->notifications()
            ->firstOrFail();

        $this->actingAs($otherUser)
            ->put(
                route(
                    'notifications.read',
                    $notification->id
                )
            )
            ->assertNotFound();
    }

    public function test_user_can_mark_own_notification_as_read(): void
    {
        $user = User::factory()->create();

        $user->notify(
            new SystemNotification(
                title: 'Read Me',
                message: 'Mark this notification as read.'
            )
        );

        $notification = $user
            ->notifications()
            ->firstOrFail();

        $this->actingAs($user)
            ->put(
                route(
                    'notifications.read',
                    $notification->id
                )
            )
            ->assertRedirect();

        $this->assertNotNull(
            $notification
                ->fresh()
                ->read_at
        );
    }
}
