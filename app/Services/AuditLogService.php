<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class AuditLogService
{
    /**
     * @param array<string, mixed>|null $oldValues
     * @param array<string, mixed>|null $newValues
     */
    public function log(
        ?User $actor,
        string $entityType,
        int|string|null $entityId,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): void {
        AuditLog::query()->create([
            'user_id' => $actor?->id,
            'entity_type' => $entityType,
            'entity_id' => $entityId !== null ? (int) $entityId : null,
            'action' => $action,
            'old_values' => $oldValues !== null ? $this->normalizeArray($oldValues) : null,
            'new_values' => $newValues !== null ? $this->normalizeArray($newValues) : null,
            'description' => $description,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function normalizeArray(array $payload): array
    {
        $normalized = [];

        foreach ($payload as $key => $value) {
            $normalized[$key] = $this->normalizeValue($value);
        }

        return $normalized;
    }

    private function normalizeValue(mixed $value): mixed
    {
        if (is_null($value) || is_bool($value) || is_int($value) || is_float($value) || is_string($value)) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format(DateTimeInterface::ATOM);
        }

        if ($value instanceof Arrayable) {
            return $this->normalizeValue($value->toArray());
        }

        if ($value instanceof JsonSerializable) {
            return $this->normalizeValue($value->jsonSerialize());
        }

        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $arrayKey => $arrayValue) {
                if (is_int($arrayKey) || is_string($arrayKey)) {
                    $normalized[$arrayKey] = $this->normalizeValue($arrayValue);
                }
            }

            return $normalized;
        }

        return (string) $value;
    }
}
