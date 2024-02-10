<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\DTO\AlbumFilter\AlbumFilters;
use App\Repository\DexAvailabilitiesRepository;
use App\Tests\Common\Traits\CounterTrait\CountDexAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DexAvailabilitiesRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountDexAvailabilityTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testRemoveAll(): void
    {
        $this->assertGreaterThan(0, $this->getDexAvailabilityCount());

        /** @var DexAvailabilitiesRepository $repo */
        $repo = static::getContainer()->get(DexAvailabilitiesRepository::class);
        $repo->removeAll();

        $this->assertEquals(0, $this->getDexAvailabilityCount());
    }

    public function testGetTotal(): void
    {
        /** @var DexAvailabilitiesRepository $repo */
        $repo = static::getContainer()->get(DexAvailabilitiesRepository::class);

        $totalCount = $repo->getTotal(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([])
        );

        $this->assertEquals(22, $totalCount);
    }

    public function testGetTotalDifferentTrainer(): void
    {
        /** @var DexAvailabilitiesRepository $repo */
        $repo = static::getContainer()->get(DexAvailabilitiesRepository::class);

        $totalCount = $repo->getTotal(
            'bd307a3ec329e10a2cff8fb87480823da114f8f4',
            'home',
            AlbumFilters::createFromArray([])
        );

        $this->assertEquals(22, $totalCount);
    }

    /**
     * @param string[][] $filters
     *
     * @dataProvider providerGetTotalFilters
     */
    public function testGetTotalFilters(array $filters, int $expectedTotalCount): void
    {
        /** @var DexAvailabilitiesRepository $repo */
        $repo = static::getContainer()->get(DexAvailabilitiesRepository::class);

        $totalCount = $repo->getTotal(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray($filters)
        );

        $this->assertEquals($expectedTotalCount, $totalCount);
    }

    /**
     * @return string[][][][]|int[][]
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function providerGetTotalFilters(): array
    {
        return [
            'primary_type' => [
                'filters' => [
                    'primaryTypes' => [
                        'grass',
                    ],
                ],
                'totalCount' => 6,
            ],
            'primary_type_null' => [
                'filters' => [
                    'primaryTypes' => [
                        'null',
                    ],
                ],
                'totalCount' => 1,
            ],
            'secondary_type' => [
                'filters' => [
                    'secondaryTypes' => [
                        'normal',
                    ],
                ],
                'totalCount' => 3,
            ],
            'secondary_type_null' => [
                'filters' => [
                    'secondaryTypes' => [
                        'null',
                    ],
                ],
                'totalCount' => 9,
            ],
            'primary_and_secondary_types' => [
                'filters' => [
                    'primaryTypes' => [
                        'bug',
                    ],
                    'secondaryTypes' => [
                        'flying',
                    ],
                ],
                'totalCount' => 3,
            ],
            'any_type' => [
                'filters' => [
                    'anyTypes' => [
                        'normal',
                    ],
                ],
                'totalCount' => 7,
            ],
            'category_form' => [
                'filters' => [
                    'categoryForms' => [
                        'starter',
                    ],
                ],
                'totalCount' => 2,
            ],
            'category_form_null' => [
                'filters' => [
                    'categoryForms' => [
                        'null',
                    ],
                ],
                'totalCount' => 20,
            ],
            'regional_form' => [
                'filters' => [
                    'regionalForms' => [
                        'alolan',
                    ],
                ],
                'totalCount' => 3,
            ],
            'regional_form_null' => [
                'filters' => [
                    'regionalForms' => [
                        'null',
                    ],
                ],
                'totalCount' => 19,
            ],
            'special_form' => [
                'filters' => [
                    'specialForms' => [
                        'gigantamax',
                    ],
                ],
                'totalCount' => 2,
            ],
            'special_form_null' => [
                'filters' => [
                    'specialForms' => [
                        'null',
                    ],
                ],
                'totalCount' => 18,
            ],
            'special_forms' => [
                'filters' => [
                    'specialForms' => [
                        'gigantamax',
                        'mega',
                    ],
                ],
                'totalCount' => 3,
            ],
            'variant_form' => [
                'filters' => [
                    'variantForms' => [
                        'gender',
                    ],
                ],
                'totalCount' => 4,
            ],
            'variant_form_null' => [
                'filters' => [
                    'variantForms' => [
                        'null',
                    ],
                ],
                'totalCount' => 18,
            ],
            'catch_state' => [
                'filters' => [
                    'catchStates' => [
                        'maybe',
                    ],
                ],
                'totalCount' => 3,
            ],
            'catch_state_null' => [
                'filters' => [
                    'catchStates' => [
                        'null',
                    ],
                ],
                'totalCount' => 1,
            ],
            'catch_states' => [
                'filters' => [
                    'catchStates' => [
                        'maybe',
                        'maybenot',
                    ],
                ],
                'totalCount' => 6,
            ],
            'empty' => [
                'filters' => [
                    'primaryTypes' => [
                        '',
                    ],
                    'secondaryTypes' => [
                        '',
                    ],
                    'anyTypes' => [
                        '',
                    ],
                    'categoryForms' => [
                        '',
                    ],
                    'regionalForms' => [
                        '',
                    ],
                    'specialForms' => [
                        '',
                    ],
                    'variantForms' => [
                        '',
                    ],
                    'catchStates' => [
                        '',
                    ],
                ],
                'totalCount' => 22,
            ],
        ];
    }
}
