<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\Response\ActionLogResponse;
use App\Factory\ActionLogResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ActionLogResponseFactory::class)]
final class ActionLogResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromSqlRowCreatesEntryWithAllFieldsPresent(): void
    {
        $row = [
            'type_action' => 'update_pokemons',
            'row_number' => 1,
            'created_at' => '2026-05-25 10:00:00+00',
            'done_at' => '2026-05-25 10:01:00+00',
            'execution_time' => '60.123456',
            'details' => '{"nb_pokemons":"1008"}',
            'error_trace' => null,
        ];

        $entry = ActionLogResponseFactory::fromSqlRow($row);

        self::assertSame('2026-05-25 10:00:00+00', $entry->createdAt);
        self::assertSame('2026-05-25 10:01:00+00', $entry->doneAt);
        self::assertSame(60, $entry->executionTime);
        self::assertSame(['nb_pokemons' => '1008'], $entry->details);
        self::assertNull($entry->errorTrace);
    }

    #[Test]
    public function fromSqlRowHandlesAllNullOptionals(): void
    {
        $row = [
            'type_action' => 'update_pokemons',
            'row_number' => 2,
            'created_at' => '2026-05-25 09:00:00+00',
            'done_at' => null,
            'execution_time' => null,
            'details' => null,
            'error_trace' => null,
        ];

        $entry = ActionLogResponseFactory::fromSqlRow($row);

        self::assertSame('2026-05-25 09:00:00+00', $entry->createdAt);
        self::assertNull($entry->doneAt);
        self::assertNull($entry->executionTime);
        self::assertNull($entry->details);
        self::assertNull($entry->errorTrace);
    }

    #[Test]
    public function fromSqlRowSetsErrorTrace(): void
    {
        $row = [
            'type_action' => 'update_pokemons',
            'row_number' => 1,
            'created_at' => '2026-05-25 10:00:00+00',
            'done_at' => '2026-05-25 10:01:00+00',
            'execution_time' => '5',
            'details' => null,
            'error_trace' => 'Exception: something went wrong',
        ];

        $entry = ActionLogResponseFactory::fromSqlRow($row);

        self::assertSame('Exception: something went wrong', $entry->errorTrace);
    }

    #[Test]
    public function fromSqlRowTruncatesExecutionTimeAtDecimalPoint(): void
    {
        $row = [
            'type_action' => 'update_pokemons',
            'row_number' => 1,
            'created_at' => '2026-05-25 10:00:00+00',
            'done_at' => '2026-05-25 10:00:01+00',
            'execution_time' => '1.999999',
            'details' => null,
            'error_trace' => null,
        ];

        $entry = ActionLogResponseFactory::fromSqlRow($row);

        self::assertSame(1, $entry->executionTime);
    }

    #[Test]
    public function fromSqlRowsAssignsCurrentForRowNumberOne(): void
    {
        $rows = [
            [
                'type_action' => 'update_pokemons',
                'row_number' => 1,
                'created_at' => '2026-05-25 10:00:00+00',
                'done_at' => null,
                'execution_time' => null,
                'details' => null,
                'error_trace' => null,
            ],
        ];

        $result = ActionLogResponseFactory::fromSqlRows($rows);

        $entry = $this->findByActionType($result, 'update_pokemons');
        self::assertNotNull($entry->current);
        self::assertNull($entry->last);
        self::assertSame('2026-05-25 10:00:00+00', $entry->current->createdAt);
    }

    #[Test]
    public function fromSqlRowsAssignsLastForRowNumberTwo(): void
    {
        $rows = [
            [
                'type_action' => 'update_pokemons',
                'row_number' => 2,
                'created_at' => '2026-05-24 09:00:00+00',
                'done_at' => null,
                'execution_time' => null,
                'details' => null,
                'error_trace' => null,
            ],
        ];

        $result = ActionLogResponseFactory::fromSqlRows($rows);

        $entry = $this->findByActionType($result, 'update_pokemons');
        self::assertNull($entry->current);
        self::assertNotNull($entry->last);
        self::assertSame('2026-05-24 09:00:00+00', $entry->last->createdAt);
    }

    #[Test]
    public function fromSqlRowsGroupsCurrentAndLastUnderSameTypeAction(): void
    {
        $rows = [
            [
                'type_action' => 'update_pokemons',
                'row_number' => 1,
                'created_at' => '2026-05-25 10:00:00+00',
                'done_at' => '2026-05-25 10:01:00+00',
                'execution_time' => '60',
                'details' => null,
                'error_trace' => null,
            ],
            [
                'type_action' => 'update_pokemons',
                'row_number' => 2,
                'created_at' => '2026-05-24 09:00:00+00',
                'done_at' => null,
                'execution_time' => null,
                'details' => null,
                'error_trace' => null,
            ],
        ];

        $result = ActionLogResponseFactory::fromSqlRows($rows);

        self::assertCount(1, $result);
        $entry = $result[0];
        self::assertSame('update_pokemons', $entry->actionType);
        self::assertNotNull($entry->current);
        self::assertNotNull($entry->last);
        self::assertSame('2026-05-25 10:00:00+00', $entry->current->createdAt);
        self::assertSame('2026-05-24 09:00:00+00', $entry->last->createdAt);
    }

    #[Test]
    public function fromSqlRowsCreatesSeparateEntriesForDifferentTypeActions(): void
    {
        $rows = [
            [
                'type_action' => 'update_pokemons',
                'row_number' => 1,
                'created_at' => '2026-05-25 10:00:00+00',
                'done_at' => null,
                'execution_time' => null,
                'details' => null,
                'error_trace' => null,
            ],
            [
                'type_action' => 'update_labels',
                'row_number' => 1,
                'created_at' => '2026-05-25 08:00:00+00',
                'done_at' => null,
                'execution_time' => null,
                'details' => null,
                'error_trace' => null,
            ],
        ];

        $result = ActionLogResponseFactory::fromSqlRows($rows);

        self::assertCount(2, $result);
        self::assertSame('update_labels', $result[0]->actionType);
        self::assertSame('update_pokemons', $result[1]->actionType);
    }

    #[Test]
    public function fromSqlRowsHandlesEmptyArray(): void
    {
        $result = ActionLogResponseFactory::fromSqlRows([]);

        self::assertCount(0, $result);
    }

    #[Test]
    public function fromSqlRowsReturnsAlphabeticallySortedList(): void
    {
        $rows = [
            [
                'type_action' => 'update_pokemons',
                'row_number' => 1,
                'created_at' => '2026-05-25 10:00:00+00',
                'done_at' => null,
                'execution_time' => null,
                'details' => null,
                'error_trace' => null,
            ],
            [
                'type_action' => 'calculate_dex_availabilities',
                'row_number' => 1,
                'created_at' => '2026-05-25 09:00:00+00',
                'done_at' => null,
                'execution_time' => null,
                'details' => null,
                'error_trace' => null,
            ],
            [
                'type_action' => 'update_labels',
                'row_number' => 1,
                'created_at' => '2026-05-25 08:00:00+00',
                'done_at' => null,
                'execution_time' => null,
                'details' => null,
                'error_trace' => null,
            ],
        ];

        $result = ActionLogResponseFactory::fromSqlRows($rows);

        self::assertCount(3, $result);
        self::assertSame('calculate_dex_availabilities', $result[0]->actionType);
        self::assertSame('update_labels', $result[1]->actionType);
        self::assertSame('update_pokemons', $result[2]->actionType);
    }

    /**
     * @param list<ActionLogResponse> $list
     */
    private function findByActionType(array $list, string $actionType): ActionLogResponse
    {
        foreach ($list as $entry) {
            if ($entry->actionType === $actionType) {
                return $entry;
            }
        }

        self::fail(sprintf('No ActionLogResponse found with actionType "%s".', $actionType));
    }
}
