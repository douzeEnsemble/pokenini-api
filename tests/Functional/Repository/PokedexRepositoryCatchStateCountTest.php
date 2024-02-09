<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\DTO\AlbumFilter\AlbumFilters;
use App\Repository\PokedexRepository;
use App\Tests\Common\Traits\GetterTrait\GetPokedexTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PokedexRepositoryCatchStateCountTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use GetPokedexTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetCatchStatesCounts(): void
    {
        /** @var PokedexRepository $repo */
        $repo = static::getContainer()->get(PokedexRepository::class);

        $counts = $repo->getCatchStatesCounts(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'redgreenblueyellow',
            AlbumFilters::createFromArray([]),
        );

        $this->assertEquals(
            [
                [
                    'count' => 1,
                    'slug' => 'no',
                    'name' => 'No',
                    'french_name' => 'Non',
                ],
                [
                    'count' => 1,
                    'slug' => 'maybe',
                    'name' => 'Maybe',
                    'french_name' => 'Peut être',
                ],
                [
                    'count' => 2,
                    'slug' => 'maybenot',
                    'name' => 'Maybe not',
                    'french_name' => 'Peut être pas',
                ],
                [
                    'count' => 0,
                    'slug' => 'yes',
                    'name' => 'Yes',
                    'french_name' => 'Oui',
                ],
            ],
            $counts
        );
    }

    /**
     * @param string[][] $filters
     * @param string[][][]|int[][][] $expectedCounts
     *
     * @dataProvider providerGetCatchStatesCountsFilters
     */
    public function testGetCatchStatesCountsFilters(
        array $filters,
        array $expectedCounts
    ): void {
        /** @var PokedexRepository $repo */
        $repo = static::getContainer()->get(PokedexRepository::class);

        $counts = $repo->getCatchStatesCounts(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray($filters),
        );

        $this->assertEquals($expectedCounts[0], $counts[0]['count']);
        $this->assertEquals($expectedCounts[1], $counts[1]['count']);
        $this->assertEquals($expectedCounts[2], $counts[2]['count']);
        $this->assertEquals($expectedCounts[3], $counts[3]['count']);
    }

    /**
     * @return string[][][][]|int[][][]
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function providerGetCatchStatesCountsFilters(): array
    {
        return [
            'primary_type' => [
                [
                    'primaryTypes' => [
                        'grass',
                    ],
                ],
                [
                    6,
                    0,
                    0,
                    0,
                ],
            ],
            'secondary_type' => [
                [
                    'secondaryTypes' => [
                        'normal',
                    ],
                ],
                [
                    1,
                    0,
                    2,
                    0,
                ],
            ],
            'primary_and_secondary_types' => [
                [
                    'primaryTypes' => [
                        'bug',
                    ],
                    'secondaryTypes' => [
                        'flying',
                    ],
                ],
                [
                    1,
                    0,
                    0,
                    2,
                ],
            ],
            'any_types' => [
                [
                    'anyTypes' => [
                        'normal',
                    ],
                ],
                [
                    1,
                    2,
                    2,
                    2,
                ],
            ],
            'category_form' => [
                [
                    'categoryForms' => [
                        'starter',
                    ],
                ],
                [
                    1,
                    0,
                    0,
                    1,
                ],
            ],
            'regional_form' => [
                [
                    'regionalForms' => [
                        'alolan',
                    ],
                ],
                [
                    1,
                    0,
                    2,
                    0,
                ],
            ],
            'special_form' => [
                [
                    'specialForms' => [
                        'gigantamax',
                    ],
                ],
                [
                    2,
                    0,
                    0,
                    0,
                ],
            ],
            'special_forms' => [
                [
                    'specialForms' => [
                        'gigantamax',
                        'mega',
                    ],
                ],
                [
                    3,
                    0,
                    0,
                    0,
                ],
            ],
            'variant_form' => [
                [
                    'variantForms' => [
                        'gender',
                    ],
                ],
                [
                    1,
                    2,
                    0,
                    1,
                ],
            ],
            'catch_state' => [
                [
                    'catchStates' => [
                        'maybe',
                    ],
                ],
                [
                    0,
                    3,
                    0,
                    0,
                ],
            ],
            'catch_states' => [
                [
                    'catchStates' => [
                        'maybe',
                        'maybenot',
                    ],
                ],
                [
                    0,
                    3,
                    3,
                    0,
                ],
            ],
        ];
    }
}
