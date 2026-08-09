<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Repository\PokedexRepository;
use App\Service\PokedexService;
use App\Service\PropagateCatchStateService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokedexService::class)]
final class PokedexServiceTest extends TestCase
{
    #[Test]
    public function getCatchStateCountsDefinedByTrainer(): void
    {
        $repository = $this->createMock(PokedexRepository::class);
        $repository->expects($this->once())
            ->method('getCatchStateCountsDefinedByTrainer')
            ->willReturn(
                [
                    [
                        'nb' => 28,
                        'trainer' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                    ],
                    [
                        'nb' => 3,
                        'trainer' => 'bd307a3ec329e10a2cff8fb87480823da114f8f4',
                    ],
                ]
            )
        ;

        $service = new PokedexService($repository, $this->createStub(PropagateCatchStateService::class));

        $this->assertEquals(
            [
                [
                    'nb' => 28,
                    'trainer' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                ],
                [
                    'nb' => 3,
                    'trainer' => 'bd307a3ec329e10a2cff8fb87480823da114f8f4',
                ],
            ],
            $service->getCatchStateCountsDefinedByTrainer()
        );
    }

    #[Test]
    public function getDexUsage(): void
    {
        $repository = $this->createMock(PokedexRepository::class);
        $repository->expects($this->once())
            ->method('getDexUsage')
            ->willReturn(
                [
                    [
                        'nb' => 2,
                        'name' => 'Red / Green / Blue / Yellow',
                        'french_name' => 'Rouge / Vert / Bleu / Jaune',
                    ],
                    [
                        'nb' => 2,
                        'name' => 'Gold / Silver / Crystal',
                        'french_name' => 'Or / Argent / Cristal',
                    ],
                    [
                        'nb' => 2,
                        'name' => 'Home',
                        'french_name' => 'Home',
                    ],
                ]
            )
        ;

        $service = new PokedexService($repository, $this->createStub(PropagateCatchStateService::class));

        $this->assertEquals(
            [
                [
                    'nb' => 2,
                    'name' => 'Red / Green / Blue / Yellow',
                    'french_name' => 'Rouge / Vert / Bleu / Jaune',
                ],
                [
                    'nb' => 2,
                    'name' => 'Gold / Silver / Crystal',
                    'french_name' => 'Or / Argent / Cristal',
                ],
                [
                    'nb' => 2,
                    'name' => 'Home',
                    'french_name' => 'Home',
                ],
            ],
            $service->getDexUsage()
        );
    }

    #[Test]
    public function getCatchStateUsage(): void
    {
        $repository = $this->createMock(PokedexRepository::class);
        $repository->expects($this->once())
            ->method('getCatchStateUsage')
            ->willReturn(
                [
                    [
                        'nb' => 11,
                        'name' => 'No',
                        'french_name' => 'Non',
                        'color' => '#e57373',
                    ],
                    [
                        'nb' => 4,
                        'name' => 'Maybe',
                        'french_name' => 'Peut être',
                        'color' => 'blue',
                    ],
                    [
                        'nb' => 5,
                        'name' => 'Maybe not',
                        'french_name' => 'Peut être pas',
                        'color' => 'yellow',
                    ],
                    [
                        'nb' => 11,
                        'name' => 'Yes',
                        'french_name' => 'Oui',
                        'color' => '#66bb6a',
                    ],
                ]
            )
        ;

        $service = new PokedexService($repository, $this->createStub(PropagateCatchStateService::class));

        $this->assertEquals(
            [
                [
                    'nb' => 11,
                    'name' => 'No',
                    'french_name' => 'Non',
                    'color' => '#e57373',
                ],
                [
                    'nb' => 4,
                    'name' => 'Maybe',
                    'french_name' => 'Peut être',
                    'color' => 'blue',
                ],
                [
                    'nb' => 5,
                    'name' => 'Maybe not',
                    'french_name' => 'Peut être pas',
                    'color' => 'yellow',
                ],
                [
                    'nb' => 11,
                    'name' => 'Yes',
                    'french_name' => 'Oui',
                    'color' => '#66bb6a',
                ],
            ],
            $service->getCatchStateUsage()
        );
    }

    #[Test]
    public function upsertReturnsOnlyOriginWhenNothingChanged(): void
    {
        $repository = $this->createMock(PokedexRepository::class);
        $repository->expects($this->once())
            ->method('upsert')
            ->with('bd307a3ec329e10a2cff8fb87480823da114f8f4', 'bw2', 'pichu', 'yes')
            ->willReturn(null)
        ;

        $propagateCatchStateService = $this->createMock(PropagateCatchStateService::class);
        $propagateCatchStateService->expects($this->never())->method('propagate');

        $service = new PokedexService($repository, $propagateCatchStateService);

        $this->assertSame(
            ['bw2'],
            $service->upsert('bd307a3ec329e10a2cff8fb87480823da114f8f4', 'bw2', 'pichu', 'yes')
        );
    }

    #[Test]
    public function upsertPropagatesWhenOriginChanged(): void
    {
        $repository = $this->createMock(PokedexRepository::class);
        $repository->expects($this->once())
            ->method('upsert')
            ->with('bd307a3ec329e10a2cff8fb87480823da114f8f4', 'bw2', 'pichu', 'yes')
            ->willReturn('trainer-dex-uuid')
        ;

        $propagateCatchStateService = $this->createMock(PropagateCatchStateService::class);
        $propagateCatchStateService->expects($this->once())
            ->method('propagate')
            ->with('bd307a3ec329e10a2cff8fb87480823da114f8f4', 'trainer-dex-uuid', 'pichu', 'yes')
            ->willReturn(['bw2_shiny'])
        ;

        $service = new PokedexService($repository, $propagateCatchStateService);

        $this->assertSame(
            ['bw2', 'bw2_shiny'],
            $service->upsert('bd307a3ec329e10a2cff8fb87480823da114f8f4', 'bw2', 'pichu', 'yes')
        );
    }

    #[Test]
    public function upsertPropagatesNothingWhenCascadeReturnsEmptyList(): void
    {
        $repository = $this->createMock(PokedexRepository::class);
        $repository->expects($this->once())
            ->method('upsert')
            ->willReturn('trainer-dex-uuid')
        ;

        $propagateCatchStateService = $this->createMock(PropagateCatchStateService::class);
        $propagateCatchStateService->expects($this->once())
            ->method('propagate')
            ->willReturn([])
        ;

        $service = new PokedexService($repository, $propagateCatchStateService);

        $this->assertSame(
            ['bw2'],
            $service->upsert('bd307a3ec329e10a2cff8fb87480823da114f8f4', 'bw2', 'pichu', 'yes')
        );
    }
}
