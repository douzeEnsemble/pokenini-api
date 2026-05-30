<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Repository\CollectionsRepository;
use App\Service\CollectionsService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CollectionsService::class)]
final class CollectionsServiceTest extends TestCase
{
    private CollectionsRepository&MockObject $repository;
    private CollectionsService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CollectionsRepository::class);
        $this->service = new CollectionsService($this->repository);
    }

    #[Test]
    public function getAllReturnsRepositoryData(): void
    {
        $expectedData = [
            [
                'slug' => 'swshdynamaxadventuresbosses',
                'name' => 'Sword, Shield - Dynamax Adventures bosses',
                'french_name' => 'Sword, Shield - Boss des expéditions Dynamax',
            ],
            [
                'slug' => 'pogodynamax',
                'name' => 'Pokemon Go - Dynamax',
                'french_name' => 'Pokemon Go - Dynamax',
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
