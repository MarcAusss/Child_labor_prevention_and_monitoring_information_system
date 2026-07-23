<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\ChildLaborer;
use App\Models\User;
use App\Models\WorkHazard;
use DateTimeInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Stringable;
use Throwable;

class ActivityLogger
{
    /**
     * @param array<string, mixed> $oldValues
     * @param array<string, mixed> $newValues
     * @param array<string, mixed> $metadata
     */
    public function log(
        string $action,
        string $description,
        ?Model $subject = null,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = [],
        ?User $actor = null,
        ?int $childLaborerId = null
    ): ?ActivityLog {
        if (! config(
            'activity-log.enabled',
            true
        )) {
            return null;
        }

        if (
            app()->runningInConsole()
            && ! config(
                'activity-log.log_console',
                false
            )
        ) {
            return null;
        }

        $actor ??= $this->authenticatedUser();

        $request = $this->currentRequest();

        return ActivityLog::query()->create([
            'user_id' =>
                $actor?->id,

            'actor_name' =>
                $actor?->name,

            'role_name' =>
                $actor?->role?->name,

            'child_laborer_id' =>
                $this->resolveChildLaborerId(
                    $subject,
                    $childLaborerId
                ),

            'action' =>
                Str::lower(
                    trim($action)
                ),

            'entity_type' =>
                $subject
                    ? $subject::class
                    : null,

            'entity_id' =>
                $subject?->getKey(),

            'description' =>
                trim($description),

            'old_values' =>
                $oldValues !== []
                    ? $this->sanitizeArray(
                        $oldValues
                    )
                    : null,

            'new_values' =>
                $newValues !== []
                    ? $this->sanitizeArray(
                        $newValues
                    )
                    : null,

            'metadata' =>
                $metadata !== []
                    ? $this->sanitizeArray(
                        $metadata
                    )
                    : null,

            'ip_address' =>
                $request?->ip(),

            'user_agent' =>
                $request?->userAgent(),

            'request_method' =>
                $request?->method(),

            'route_name' =>
                $request?->route()?->getName(),

            'url' =>
                $request?->fullUrl(),

            'created_at' =>
                now(),
        ]);
    }

    /**
     * @param array<string, mixed> $oldValues
     * @param array<string, mixed> $newValues
     */
    public function modelChange(
        Model $subject,
        string $action,
        string $description,
        array $oldValues = [],
        array $newValues = []
    ): ?ActivityLog {
        return $this->log(
            action: $action,
            description: $description,
            subject: $subject,
            oldValues: $oldValues,
            newValues: $newValues
        );
    }

    private function authenticatedUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User
            ? $user
            : null;
    }

    private function currentRequest(): ?Request
    {
        if (! app()->bound('request')) {
            return null;
        }

        try {
            $request = request();

            return $request instanceof Request
                ? $request
                : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function resolveChildLaborerId(
        ?Model $subject,
        ?int $explicitChildLaborerId
    ): ?int {
        if ($explicitChildLaborerId !== null) {
            return $explicitChildLaborerId;
        }

        if (! $subject) {
            return null;
        }

        if ($subject instanceof ChildLaborer) {
            return (int) $subject->getKey();
        }

        $attributes = $subject->getAttributes();

        if (
            array_key_exists(
                'child_laborer_id',
                $attributes
            )
            && $attributes['child_laborer_id']
                !== null
        ) {
            return (int) $attributes[
                'child_laborer_id'
            ];
        }

        if ($subject instanceof WorkHazard) {
            $childLaborerId = $subject
                ->employmentRecord()
                ->value('child_laborer_id');

            return $childLaborerId !== null
                ? (int) $childLaborerId
                : null;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $values
     *
     * @return array<string, mixed>
     */
    private function sanitizeArray(
        array $values
    ): array {
        $sanitized = [];

        foreach ($values as $key => $value) {
            $key = (string) $key;

            if ($this->isRedactedField($key)) {
                $sanitized[$key] = '[REDACTED]';

                continue;
            }

            $sanitized[$key] =
                $this->sanitizeValue(
                    $value
                );
        }

        return $sanitized;
    }

    private function sanitizeValue(
        mixed $value
    ): mixed {
        if ($value === null) {
            return null;
        }

        if (
            is_bool($value)
            || is_int($value)
            || is_float($value)
        ) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format(
                DATE_ATOM
            );
        }

        if ($value instanceof Model) {
            return [
                'type' => $value::class,
                'id' => $value->getKey(),
            ];
        }

        if ($value instanceof Authenticatable) {
            return [
                'type' => $value::class,
                'id' => $value->getAuthIdentifier(),
            ];
        }

        if (is_array($value)) {
            return $this->sanitizeArray(
                $value
            );
        }

        if ($value instanceof Stringable) {
            $value = (string) $value;
        }

        if (is_string($value)) {
            return Str::limit(
                $value,
                (int) config(
                    'activity-log.maximum_string_length',
                    5000
                ),
                '…'
            );
        }

        return Str::limit(
            json_encode(
                $value,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            ) ?: get_debug_type($value),
            (int) config(
                'activity-log.maximum_string_length',
                5000
            ),
            '…'
        );
    }

    private function isRedactedField(
        string $field
    ): bool {
        $field = Str::lower($field);

        foreach (
            config(
                'activity-log.redacted_fields',
                []
            ) as $redactedField
        ) {
            $redactedField = Str::lower(
                (string) $redactedField
            );

            if (
                $field === $redactedField
                || str_contains(
                    $field,
                    $redactedField
                )
            ) {
                return true;
            }
        }

        return false;
    }
}