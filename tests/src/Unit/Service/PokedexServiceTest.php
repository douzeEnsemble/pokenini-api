<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Repository\PokedexRepository;
use App\Service\PokedexService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokedexService::class)]
class PokedexServiceTest extends TestCase
{
    public function testGetCatchStateCountsDefinedByTrainer(): void
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

        $service = new PokedexService($repository);

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

    public function testGetDexUsage(): void
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

        $service = new PokedexService($repository);

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

    public function testGetCatchStateUsage(): void
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

        $service = new PokedexService($repository);

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

    public function testUpsert(): void
    {
        $repository = $this->createMock(PokedexRepository::class);
        $repository->expects($this->once())
            ->method('upsert')
            ->with(
                'bd307a3ec329e10a2cff8fb87480823da114f8f4',
                'bw2',
                'pichu',
                'yes',
            )
        ;

        $service = new PokedexService($repository);

        $service->upsert('bd307a3ec329e10a2cff8fb87480823da114f8f4', 'bw2', 'pichu', 'yes');
    }
}
