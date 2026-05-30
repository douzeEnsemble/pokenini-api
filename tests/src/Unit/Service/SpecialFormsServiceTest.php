<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Repository\SpecialFormsRepository;
use App\Service\SpecialFormsService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(SpecialFormsService::class)]
final class SpecialFormsServiceTest extends TestCase
{
    private MockObject&SpecialFormsRepository $repository;
    private SpecialFormsService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(SpecialFormsRepository::class);
        $this->service = new SpecialFormsService($this->repository);
    }

    #[Test]
    public function getAllReturnsRepositoryData(): void
    {
        $expectedData = [
            ['slug' => 'mega', 'name' => 'Mega', 'french_name' => 'Mega'],
            ['slug' => 'gigantamax', 'name' => 'Gigantamax', 'french_name' => 'Gigamax'],
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
