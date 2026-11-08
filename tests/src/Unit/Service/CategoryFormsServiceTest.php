<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Repository\CategoryFormsRepository;
use App\Service\CategoryFormsService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CategoryFormsService::class)]
final class CategoryFormsServiceTest extends TestCase
{
    private CategoryFormsRepository&MockObject $repository;
    private CategoryFormsService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CategoryFormsRepository::class);
        $this->service = new CategoryFormsService($this->repository, 'en');
    }

    #[Test]
    public function getAllReturnsRepositoryData(): void
    {
        $expectedData = [
            ['slug' => 'starter', 'name' => 'Starter', 'french_name' => 'de Départ'],
            ['slug' => 'legendary', 'name' => 'Legendary', 'french_name' => 'Légendaire'],
        ];

        $this->repository
            ->expects(self::once())
            ->method('getAll')
            ->with('en', 'en')
            ->willReturn($expectedData)
        ;

        $result = $this->service->getAll();

        self::assertSame($expectedData, $result);
    }

    #[Test]
    public function getAllAsksTheRepositoryForTheRequestedLocale(): void
    {
        $this->repository
            ->expects(self::once())
            ->method('getAll')
            ->with('fr', 'en')
            ->willReturn([])
        ;

        $this->service->getAll('fr');
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
