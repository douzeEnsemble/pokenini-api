<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Repository\RegionalFormsRepository;
use App\Service\RegionalFormsService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(RegionalFormsService::class)]
final class RegionalFormsServiceTest extends TestCase
{
    private MockObject&RegionalFormsRepository $repository;
    private RegionalFormsService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(RegionalFormsRepository::class);
        $this->service = new RegionalFormsService($this->repository);
    }

    #[Test]
    public function getAllReturnsRepositoryData(): void
    {
        $expectedData = [
            ['slug' => 'alolan', 'name' => 'Alolan', 'french_name' => "d'Alola"],
            ['slug' => 'hisuian', 'name' => 'Hisuian', 'french_name' => 'de Hisui'],
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
