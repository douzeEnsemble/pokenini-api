<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\ActionLogEntryResponse;
use App\DTO\Response\ActionLogResponse;

final class ActionLogResponseFactory
{
    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromSqlRow(array $row): ActionLogEntryResponse
    {
        /** @var null|scalar $rawDetails */
        $rawDetails = $row['details'];
        $details = null;
        if (null !== $rawDetails) {
            /** @var array<string, string> $details */
            $details = json_decode((string) $rawDetails, true);
        }

        /** @var null|scalar $rawExecutionTime */
        $rawExecutionTime = $row['execution_time'];
        $executionTime = null;
        if (null !== $rawExecutionTime) {
            [$executionTime] = explode('.', (string) $rawExecutionTime);
        }

        /** @var scalar $createdAt */
        $createdAt = $row['created_at'];

        /** @var null|scalar $doneAt */
        $doneAt = $row['done_at'];

        /** @var null|scalar $errorTrace */
        $errorTrace = $row['error_trace'];

        return new ActionLogEntryResponse(
            createdAt: (string) $createdAt,
            doneAt: null !== $doneAt ? (string) $doneAt : null,
            executionTime: $executionTime,
            details: $details,
            errorTrace: null !== $errorTrace ? (string) $errorTrace : null,
        );
    }

    /**
     * @param array<int, array<array-key, mixed>> $rows
     *
     * @return array<string, ActionLogResponse>
     */
    public static function fromSqlRows(array $rows): array
    {
        /** @var array<string, array{current: ?ActionLogEntryResponse, last: ?ActionLogEntryResponse}> $grouped */
        $grouped = [];
        foreach ($rows as $row) {
            /** @var scalar $typeAction */
            $typeAction = $row['type_action'];
            $typeActionStr = (string) $typeAction;

            /** @var scalar $rowNumber */
            $rowNumber = $row['row_number'];
            $period = 1 === (int) $rowNumber ? 'current' : 'last';

            if (!array_key_exists($typeActionStr, $grouped)) {
                $grouped[$typeActionStr] = ['current' => null, 'last' => null];
            }

            $grouped[$typeActionStr][$period] = self::fromSqlRow($row);
        }

        $result = [];
        foreach ($grouped as $typeAction => $entries) {
            $result[$typeAction] = new ActionLogResponse(
                current: $entries['current'],
                last: $entries['last'],
            );
        }

        return $result;
    }
}
