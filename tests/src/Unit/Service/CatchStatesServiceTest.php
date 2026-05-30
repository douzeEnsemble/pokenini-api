<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Repository\CatchStatesRepository;
use App\Service\CatchStatesService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CatchStatesService::class)]
final class CatchStatesServiceTest extends TestCase
{
    private CatchStatesRepository&MockObject $repository;
    private CatchStatesService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CatchStatesRepository::class);
        $this->service = new CatchStatesService($this->repository);
    }

    #[Test]
    public function getAllReturnsRepositoryData(): void
    {
        $expectedData = [
            [
                'slug' => 'no',
                'name' => 'No',
                'french_name' => 'Non',
                'color' => '#e57373',
            ],
            [
                'slug' => 'yes',
                'name' => 'Yes',
                'french_name' => 'Oui',
                'color' => '#66bb6a',
            ],
        ];

        $this->repository
            ->expects(self::once())
            ->method('getAll')
            ->willReturn($expectedData)
        ;

        $result = $this->service->getAll();

        self::assertSame($expectedData, $result);
    }

    #[Test]
    public function getAllHandlesEmptyResult(): void
    {
        $this->repository
            ->expects(self::once())
            ->method('getAll')
            ->willReturn([])
        ;

        $result = $this->service->getAll();

        self::assertCount(0, $result);
    }
}
