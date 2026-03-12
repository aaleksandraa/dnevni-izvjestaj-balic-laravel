<?php

namespace App\Services;

use App\Models\ReportConfiguration;

class DailyEmailSummaryConfigurationService
{
    public const CONFIG_KEY = 'daily_email_summary';

    /**
     * @return array{
     *     service_ids: array<int>,
     *     collaborator_ids: array<int>,
     *     lead_doctor_ids: array<int>,
     *     include_new_patients: bool
     * }
     */
    public function get(): array
    {
        $defaults = $this->defaults();

        $storedConfig = ReportConfiguration::query()
            ->where('config_key', self::CONFIG_KEY)
            ->value('config_value');

        if (! is_array($storedConfig)) {
            return $defaults;
        }

        return [
            'service_ids' => $this->normalizeIds($storedConfig['service_ids'] ?? []),
            'collaborator_ids' => $this->normalizeIds($storedConfig['collaborator_ids'] ?? []),
            'lead_doctor_ids' => $this->normalizeIds($storedConfig['lead_doctor_ids'] ?? []),
            'include_new_patients' => (bool) ($storedConfig['include_new_patients'] ?? true),
        ];
    }

    /**
     * @param array<string, mixed> $input
     */
    public function save(array $input): void
    {
        $config = [
            'service_ids' => $this->normalizeIds($input['service_ids'] ?? []),
            'collaborator_ids' => $this->normalizeIds($input['collaborator_ids'] ?? []),
            'lead_doctor_ids' => $this->normalizeIds($input['lead_doctor_ids'] ?? []),
            'include_new_patients' => (bool) ($input['include_new_patients'] ?? false),
        ];

        ReportConfiguration::query()->updateOrCreate(
            ['config_key' => self::CONFIG_KEY],
            ['config_value' => $config]
        );
    }

    /**
     * @return array{
     *     service_ids: array<int>,
     *     collaborator_ids: array<int>,
     *     lead_doctor_ids: array<int>,
     *     include_new_patients: bool
     * }
     */
    private function defaults(): array
    {
        return [
            'service_ids' => [],
            'collaborator_ids' => [],
            'lead_doctor_ids' => [],
            'include_new_patients' => true,
        ];
    }

    /**
     * @param mixed $values
     * @return array<int>
     */
    private function normalizeIds(mixed $values): array
    {
        if (! is_array($values)) {
            return [];
        }

        $ids = array_map(
            static fn ($value): int => (int) $value,
            $values
        );
        $ids = array_filter($ids, static fn (int $value): bool => $value > 0);
        $ids = array_values(array_unique($ids));

        sort($ids);

        return $ids;
    }
}
