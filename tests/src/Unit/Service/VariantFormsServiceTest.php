<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Repository\VariantFormsRepository;
use App\Service\VariantFormsService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(VariantFormsService::class)]
final class VariantFormsServiceTest extends TestCase
{
    private MockObject&VariantFormsRepository $repository;
    private VariantFormsService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(VariantFormsRepository::class);
        $this->service = new VariantFormsService($this->repository);
    }

    #[Test]
    public function getAllReturnsRepositoryData(): void
    {
        $expectedData = [
            ['slug' => 'gender', 'name' => 'Gender', 'french_name' => 'Sexe'],
            ['slug' => 'unobtainable', 'name' => 'Unobtainable', 'french_name' => 'Non obtenable'],
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
