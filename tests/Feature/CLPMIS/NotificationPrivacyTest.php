<?php

namespace Tests\Feature\CLPMIS;

use App\Notifications\SystemNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesClpmisRecords;
use Tests\TestCase;

class NotificationPrivacyTest extends TestCase
{
    use RefreshDatabase;
    use CreatesClpmisRecords;

    public function test_user_cannot_read_another_users_notification(): void
    {
        $owner = $this->makeUser(
            'viewer'
        );

        $otherUser = $this->makeUser(
            'viewer'
        );

        $owner->notify(
            new SystemNotification(
                title:
                    'Private workflow notification',

                message:
                    'Only the owner may open this notification.'
            )
        );

        $notification = $owner
            ->notifications()
            ->firstOrFail();

        $this->actingAs($otherUser)
            ->get(
                route(
                    'notifications.open',
                    $notification->id
                )
            )
            ->assertNotFound();
    }

    public function test_owner_can_mark_own_notification_read(): void
    {
        $user = $this->makeUser(
            'viewer'
        );

        $user->notify(
            new SystemNotification(
                title: 'Read me',
                message:
                    'This notification belongs to the user.'
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
