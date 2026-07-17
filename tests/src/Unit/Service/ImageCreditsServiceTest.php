<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Repository\PokemonImageCreditRepository;
use App\Service\ImageCreditsService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ImageCreditsService::class)]
final class ImageCreditsServiceTest extends TestCase
{
    private MockObject&PokemonImageCreditRepository $repository;
    private ImageCreditsService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(PokemonImageCreditRepository::class);
        $this->service = new ImageCreditsService($this->repository);
    }

    #[Test]
    public function getAllReturnsRepositoryData(): void
    {
        $expected = [['source_name' => 'A', 'source_url' => 'https://a.example']];

        $this->repository
            ->expects(self::once())
            ->method('findAllDistinctSources')
            ->willReturn($expected)
        ;

        self::assertSame($expected, $this->service->getAll());
    }
}
