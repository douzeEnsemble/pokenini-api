<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Repository\DexRepository;
use App\Service\DexBannerLayersService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexBannerLayersService::class)]
final class DexBannerLayersServiceTest extends TestCase
{
    #[Test]
    public function getAll(): void
    {
        $repository = $this->createMock(DexRepository::class);
        $repository
            ->expects($this->once())
            ->method('getBannerLayers')
            ->willReturn([
                ['slug' => 'redgreenblueyellow', 'banner_layers' => 'shiny,mega'],
                ['slug' => 'xy', 'banner_layers' => 'xy'],
            ])
        ;

        $service = new DexBannerLayersService($repository);

        $this->assertSame(
            [
                'redgreenblueyellow' => ['shiny', 'mega'],
                'xy' => ['xy'],
            ],
            $service->getAll()
        );
    }

    #[Test]
    public function getAllWithNoBannerLayers(): void
    {
        $repository = $this->createMock(DexRepository::class);
        $repository
            ->expects($this->once())
            ->method('getBannerLayers')
            ->willReturn([])
        ;

        $service = new DexBannerLayersService($repository);

        $this->assertSame([], $service->getAll());
    }

    #[Test]
    public function getAllStripsEmptySegmentsFromAMalformedCell(): void
    {
        $repository = $this->createMock(DexRepository::class);
        $repository
            ->expects($this->once())
            ->method('getBannerLayers')
            ->willReturn([
                ['slug' => 'trailingcomma', 'banner_layers' => 'shiny,'],
                ['slug' => 'doublecomma', 'banner_layers' => 'shiny,,mega'],
            ])
        ;

        $service = new DexBannerLayersService($repository);

        $this->assertSame(
            [
                'trailingcomma' => ['shiny'],
                'doublecomma' => ['shiny', 'mega'],
            ],
            $service->getAll()
        );
    }
}
