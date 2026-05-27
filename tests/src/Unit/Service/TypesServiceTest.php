<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Repository\TypesRepository;
use App\Service\TypesService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TypesService::class)]
final class TypesServiceTest extends TestCase
{
    private MockObject&TypesRepository $repository;
    private TypesService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(TypesRepository::class);
        $this->service = new TypesService($this->repository);
    }

    #[Test]
    public function getAllReturnsRepositoryData(): void
    {
        $expectedData = [
            [
                'slug' => 'electric',
                'name' => 'Electric',
                'french_name' => 'Électrique',
                'color' => '#FFCC33',
            ],
            [
                'slug' => 'water',
                'name' => 'Water',
                'french_name' => 'Eau',
                'color' => '#3399FF',
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
