<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification
{
    use Queueable;

    /**
     * @param array<string, mixed> $routeParameters
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public readonly string $title,
        public readonly string $message,
        public readonly string $notificationType = 'system',
        public readonly string $severity = 'info',
        public readonly ?string $routeName = null,
        public readonly array $routeParameters = [],
        public readonly ?int $childLaborerId = null,
        public readonly ?string $actorName = null,
        public readonly array $metadata = []
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,

            'message' => $this->message,

            'notification_type' =>
                $this->notificationType,

            'severity' =>
                $this->severity,

            'route_name' =>
                $this->routeName,

            'route_parameters' =>
                $this->routeParameters,

            'child_laborer_id' =>
                $this->childLaborerId,

            'actor_name' =>
                $this->actorName,

            'metadata' =>
                $this->metadata,
        ];
    }
}
