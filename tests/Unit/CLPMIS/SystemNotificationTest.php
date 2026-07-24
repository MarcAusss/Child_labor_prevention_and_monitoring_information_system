<?php

namespace Tests\Unit\CLPMIS;

use App\Notifications\SystemNotification;
use PHPUnit\Framework\TestCase;

class SystemNotificationTest extends TestCase
{
    public function test_database_notification_payload_is_complete(): void
    {
        $notification =
            new SystemNotification(
                title:
                    'Profile approved',

                message:
                    'The child laborer profile was approved.',

                notificationType:
                    'profile',

                severity:
                    'success',

                routeName:
                    'child-laborers.show',

                routeParameters: [
                    'childLaborer' => 15,
                ],

                childLaborerId:
                    15,

                actorName:
                    'System Administrator',

                metadata: [
                    'status' => 'Approved',
                ]
            );

        $payload =
            $notification->toArray(
                new \stdClass()
            );

        $this->assertSame(
            'Profile approved',
            $payload['title']
        );

        $this->assertSame(
            'profile',
            $payload[
                'notification_type'
            ]
        );

        $this->assertSame(
            'success',
            $payload['severity']
        );

        $this->assertSame(
            15,
            $payload[
                'child_laborer_id'
            ]
        );

        $this->assertSame(
            'Approved',
            $payload[
                'metadata'
            ]['status']
        );
    }
}
