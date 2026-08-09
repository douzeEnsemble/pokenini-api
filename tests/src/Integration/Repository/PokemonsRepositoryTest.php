<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\DTO\AlbumFilter\AlbumFilters;
use App\Entity\Pokemon;
use App\Repository\PokemonsRepository;
use App\Tests\Common\Traits\CounterTrait\CountPokemonTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(PokemonsRepository::class)]
final class PokemonsRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountPokemonTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function removeAll(): void
    {
        $initCount = $this->getPokemonCount();
        $this->assertGreaterThan(0, $initCount);
        $this->assertEquals(0, $this->getPokemonDeletedCount());
        $this->assertGreaterThan(0, $this->getPokemonNotDeletedCount());

        $repo = self::getContainer()->get(PokemonsRepository::class);
        $queryBuilder = $repo->createQueryBuilder('p')
            ->update()
            ->set('p.deletedAt', ':now')
        ;

        /** @psalm-suppress QueryBuilderSetParameter */
        $queryBuilder->setParameter('now', new \DateTimeImmutable());
        $queryBuilder->getQuery()->execute();

        $this->assertEquals($initCount, $this->getPokemonCount());
        $this->assertEquals($initCount, $this->getPokemonDeletedCount());
        $this->assertEquals(0, $this->getPokemonNotDeletedCount());
    }

    #[Test]
    public function getAll(): void
    {
        $repo = self::getContainer()->get(PokemonsRepository::class);

        /** @var Pokemon[] $pokemons */
        $pokemons = $repo->getQueryAll()->getResult();

        $this->assertCount($this->getPokemonCount(), $pokemons);

        $this->assertEquals('Bulbasaur', $pokemons[0]->name);
        $this->assertEquals(1, $pokemons[0]->nationalDexNumber);
        $this->assertEquals('bulbasaur', $pokemons[0]->family);

        $this->assertTrue($pokemons[3]->bankable);
        $this->assertNull($pokemons[3]->bankableish);
        $this->assertEquals('Diamond, Pearl, Platinium', $pokemons[3]->originalGameBundle->name);

        $this->assertNull($pokemons[5]->variantForm);
        $this->assertNull($pokemons[5]->regionalForm);
        $this->assertEquals('Gigantamax', $pokemons[5]->specialForm?->name);

        $this->assertEquals('charizard', $pokemons[8]->iconName);
        $this->assertEquals('butterfree', $pokemons[11]->iconName);

        $this->assertEquals('fire', $pokemons[8]->primaryType?->slug);
        $this->assertEquals('flying', $pokemons[8]->secondaryType?->slug);
    }

    /**
     * @param string[][] $filters
     */
    #[Test]
    #[DataProvider('providerGetNToPick')]
    public function getNToPick(
        string $dexSlug,
        string $electionSlug,
        array $filters,
        int $expectedCount,
    ): void {
        $repo = self::getContainer()->get(PokemonsRepository::class);

        $list = $repo->getNToPick(
            $dexSlug,
            12,
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            $electionSlug,
            AlbumFilters::createFromArray($filters),
            1000,
        );

        $this->assertCount($expectedCount, $list);

        /** @var string $previous */
        $previous = $list[0]['pokemon_slug'];
        $max = count($list);
        for ($i = 1; $i < $max; ++$i) {
            $this->assertNotSame($previous, $list[$i]['pokemon_slug']);

            /** @var string $previous */
            $previous = $list[$i]['pokemon_slug'];
        }
    }

    /**
     * @return array<string, array{
     *  dexSlug: string,
     *  electionSlug: string,
     *  filters: array<string, string[]>,
     *  expectedCount: int,
     * }>
     */
    public static function providerGetNToPick(): array
    {
        return [
            'home-12' => [
                'dexSlug' => 'home',
                'electionSlug' => '',
                'filters' => [],
                'expectedCount' => 12,
            ],
            'redgreenblueyellow-12' => [
                'dexSlug' => 'redgreenblueyellow',
                'electionSlug' => '',
                'filters' => [],
                'expectedCount' => 7,
            ],
            'demo-affinee-12-10-10' => [
                'dexSlug' => 'redgreenblueyellow',
                'electionSlug' => 'affinee',
                'filters' => [],
                'expectedCount' => 1,
            ],
            'demo-affinee-12-2--1' => [
                'dexSlug' => 'redgreenblueyellow',
                'electionSlug' => 'affinee',
                'filters' => [],
                'expectedCount' => 1,
            ],
            'home-12-cfstarters' => [
                'dexSlug' => 'home',
                'electionSlug' => '',
                'filters' => [
                    'category_forms' => ['starter'],
                ],
                'expectedCount' => 2,
            ],
            'home-12-sfmega' => [
                'dexSlug' => 'home',
                'electionSlug' => '',
                'filters' => [
                    'special_forms' => ['mega', 'gigantamax'],
                ],
                'expectedCount' => 3,
            ],
        ];
    }

    /**
     * @param string[][] $filters
     */
    #[Test]
    #[DataProvider('providerGetNToVote')]
    public function getNToVote(
        string $dexSlug,
        string $electionSlug,
        array $filters,
        int $expectedCount,
    ): void {
        $repo = self::getContainer()->get(PokemonsRepository::class);

        $list = $repo->getNToVote(
            $dexSlug,
            12,
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            $electionSlug,
            AlbumFilters::createFromArray($filters),
            1000,
        );

        $this->assertCount($expectedCount, $list);

        /** @var string $previous */
        $previous = $list[0]['pokemon_slug'];
        $max = count($list);
        for ($i = 1; $i < $max; ++$i) {
            $this->assertNotSame($previous, $list[$i]['pokemon_slug']);

            /** @var string $previous */
            $previous = $list[$i]['pokemon_slug'];
        }
    }

    /**
     * @return array<string, array{
     *  dexSlug: string,
     *  electionSlug: string,
     *  filters: array<string, string[]>,
     *  expectedCount: int,
     * }>
     */
    public static function providerGetNToVote(): array
    {
        return [
            'home-12' => [
                'dexSlug' => 'home',
                'electionSlug' => '',
                'filters' => [],
                'expectedCount' => 0,
            ],
            'redgreenblueyellow-12' => [
                'dexSlug' => 'redgreenblueyellow',
                'electionSlug' => '',
                'filters' => [],
                'expectedCount' => 0,
            ],
            'demo-affinee-12-10-10' => [
                'dexSlug' => 'redgreenblueyellow',
                'electionSlug' => 'affinee',
                'filters' => [],
                'expectedCount' => 1,
            ],
            'demo-affinee-12-2--1' => [
                'dexSlug' => 'redgreenblueyellow',
                'electionSlug' => 'affinee',
                'filters' => [],
                'expectedCount' => 1,
            ],
            'home-12-affinee' => [
                'dexSlug' => 'home',
                'electionSlug' => 'affinee',
                'filters' => [],
                'expectedCount' => 6,
            ],
            'home-12-affinee-cfstarters' => [
                'dexSlug' => 'home',
                'electionSlug' => 'affinee',
                'filters' => [
                    'category_forms' => ['starter'],
                ],
                'expectedCount' => 1,
            ],
            'home-12-affinee-sfmega' => [
                'dexSlug' => 'home',
                'electionSlug' => 'affinee',
                'filters' => [
                    'special_forms' => ['mega', 'gigantamax'],
                ],
                'expectedCount' => 2,
            ],
        ];
    }
}
