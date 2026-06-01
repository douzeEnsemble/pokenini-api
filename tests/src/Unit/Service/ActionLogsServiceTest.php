<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Repository\ActionLogsRepository;
use App\Service\ActionLogsService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ActionLogsService::class)]
final class ActionLogsServiceTest extends TestCase
{
    private ActionLogsRepository&MockObject $repository;
    private ActionLogsService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ActionLogsRepository::class);
        $this->service = new ActionLogsService($this->repository);
    }

    #[Test]
    public function getLastestsReturnsRepositoryData(): void
    {
        $expectedData = [
            [
                'type_action' => 'update_pokemons',
                'row_number' => 1,
                'created_at' => '2026-05-25 10:00:00+00',
                'done_at' => '2026-05-25 10:01:00+00',
                'execution_time' => '60',
                'details' => null,
                'error_trace' => null,
            ],
        ];

        $this->repository
            ->expects(self::once())
            ->method('getLastests')
            ->willReturn($expectedData)
        ;

        $result = $this->service->getLastests();

        self::assertSame($expectedData, $result);
    }

    #[Test]
    public function getLastestsHandlesEmptyResult(): void
    {
        $this->repository
            ->expects(self::once())
            ->method('getLastests')
            ->willReturn([])
        ;

        $result = $this->service->getLastests();

        self::assertCount(0, $result);
    }
}
