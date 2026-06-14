# Election Vote Trainer Restructuring Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.
> **Contrainte projet:** Ne pas lancer de commandes `make`, tests ou git. L'utilisateur s'en charge.

**Goal:** Remplacer `trainer_external_id` plat par `trainer: { external_id }` dans `POST /election/vote` (body de requête + réponse), et unifier le DTO trainer avec `/reports` via `TrainerExternalIdResponse`.

**Architecture:** (1) Renommer `ReportTrainerResponse` → `TrainerExternalIdResponse` et mettre à jour tous les usages existants. (2) Modifier `ElectionVote` DTO pour parser `trainer: { external_id }` au lieu de `trainer_external_id`. (3) Modifier `ElectionVoteDataResponse` + factory pour exposer `trainer: TrainerExternalIdResponse` en réponse.

**Tech Stack:** PHP 8.5, Symfony OptionsResolver, Symfony Serializer (`#[SerializedName]`), PHPUnit

---

### Task 1 : Renommer `ReportTrainerResponse` → `TrainerExternalIdResponse`

**Files:**
- Create: `src/DTO/Response/TrainerExternalIdResponse.php`
- Delete: `src/DTO/Response/ReportTrainerResponse.php`
- Modify: `src/DTO/Response/TrainerCatchStateCountResponse.php`
- Modify: `src/Factory/ReportResponseFactory.php`
- Create: `tests/src/Unit/DTO/Response/TrainerExternalIdResponseTest.php`
- Delete: `tests/src/Unit/DTO/Response/ReportTrainerResponseTest.php`
- Modify: `tests/src/Unit/DTO/Response/TrainerCatchStateCountResponseTest.php`
- Modify: `tests/src/Unit/DTO/Response/ReportResponseTest.php`

- [ ] **Créer `TrainerExternalIdResponse.php`**

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class TrainerExternalIdResponse
{
    public function __construct(
        #[SerializedName('external_id')]
        public readonly string $externalId,
    ) {}
}
```

- [ ] **Supprimer `src/DTO/Response/ReportTrainerResponse.php`**

- [ ] **Mettre à jour `TrainerCatchStateCountResponse.php`**

Remplacer l'import `ReportTrainerResponse` par `TrainerExternalIdResponse` et mettre à jour le type du paramètre :

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class TrainerCatchStateCountResponse
{
    public function __construct(
        public readonly int $count,
        public readonly TrainerExternalIdResponse $trainer,
    ) {}
}
```

- [ ] **Mettre à jour `ReportResponseFactory.php`**

Remplacer l'import et l'instanciation :

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Response\CatchStateUsageResponse;
use App\DTO\Response\DexUsageResponse;
use App\DTO\Response\ReportCatchStateResponse;
use App\DTO\Response\ReportDexResponse;
use App\DTO\Response\ReportResponse;
use App\DTO\Response\TrainerExternalIdResponse;
use App\DTO\Response\TrainerCatchStateCountResponse;

final class ReportResponseFactory
{
    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromCatchStateCountRow(array $row): TrainerCatchStateCountResponse
    {
        /** @var scalar $count */
        $count = $row['nb'];

        /** @var scalar $trainer */
        $trainer = $row['trainer'];

        return new TrainerCatchStateCountResponse(
            count: (int) $count,
            trainer: new TrainerExternalIdResponse(
                externalId: (string) $trainer,
            ),
        );
    }

    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromDexUsageRow(array $row): DexUsageResponse
    {
        /** @var scalar $count */
        $count = $row['nb'];

        /** @var scalar $slug */
        $slug = $row['slug'];

        /** @var scalar $name */
        $name = $row['name'];

        /** @var scalar $frenchName */
        $frenchName = $row['french_name'];

        return new DexUsageResponse(
            count: (int) $count,
            dex: new ReportDexResponse(
                slug: (string) $slug,
                name: (string) $name,
                frenchName: (string) $frenchName,
            ),
        );
    }

    /**
     * @param array<array-key, mixed> $row
     */
    public static function fromCatchStateUsageRow(array $row): CatchStateUsageResponse
    {
        /** @var scalar $count */
        $count = $row['nb'];

        /** @var scalar $slug */
        $slug = $row['slug'];

        /** @var scalar $name */
        $name = $row['name'];

        /** @var scalar $frenchName */
        $frenchName = $row['french_name'];

        /** @var scalar $color */
        $color = $row['color'];

        return new CatchStateUsageResponse(
            count: (int) $count,
            catchState: new ReportCatchStateResponse(
                slug: (string) $slug,
                name: (string) $name,
                frenchName: (string) $frenchName,
                color: (string) $color,
            ),
        );
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $catchStateCounts
     * @param array<array-key, array<array-key, mixed>> $dexUsage
     * @param array<array-key, array<array-key, mixed>> $catchStateUsage
     */
    public static function fromServiceArrays(
        array $catchStateCounts,
        array $dexUsage,
        array $catchStateUsage,
    ): ReportResponse {
        return new ReportResponse(
            catchStateCountsDefinedByTrainer: array_map(self::fromCatchStateCountRow(...), $catchStateCounts),
            dexUsage: array_map(self::fromDexUsageRow(...), $dexUsage),
            catchStateUsage: array_map(self::fromCatchStateUsageRow(...), $catchStateUsage),
        );
    }
}
```

- [ ] **Créer `tests/src/Unit/DTO/Response/TrainerExternalIdResponseTest.php`**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\TrainerExternalIdResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TrainerExternalIdResponse::class)]
final class TrainerExternalIdResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $response = new TrainerExternalIdResponse(
            externalId: '7b52009b64fd0a2a49e6d8a939753077792b0554',
        );

        self::assertSame('7b52009b64fd0a2a49e6d8a939753077792b0554', $response->externalId);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $response = new TrainerExternalIdResponse(
            externalId: 'bd307a3ec329e10a2cff8fb87480823da114f8f4',
        );

        self::assertSame('bd307a3ec329e10a2cff8fb87480823da114f8f4', $response->externalId);
    }
}
```

- [ ] **Supprimer `tests/src/Unit/DTO/Response/ReportTrainerResponseTest.php`**

- [ ] **Mettre à jour `TrainerCatchStateCountResponseTest.php`**

Remplacer l'import `ReportTrainerResponse` par `TrainerExternalIdResponse` et mettre à jour les instanciations :

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\TrainerExternalIdResponse;
use App\DTO\Response\TrainerCatchStateCountResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TrainerCatchStateCountResponse::class)]
final class TrainerCatchStateCountResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $trainer = new TrainerExternalIdResponse(externalId: '7b52009b64fd0a2a49e6d8a939753077792b0554');
        $response = new TrainerCatchStateCountResponse(
            count: 28,
            trainer: $trainer,
        );

        self::assertSame(28, $response->count);
        self::assertSame($trainer, $response->trainer);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $trainer = new TrainerExternalIdResponse(externalId: 'bd307a3ec329e10a2cff8fb87480823da114f8f4');
        $response = new TrainerCatchStateCountResponse(
            count: 3,
            trainer: $trainer,
        );

        self::assertSame(3, $response->count);
        self::assertSame($trainer, $response->trainer);
    }
}
```

- [ ] **Mettre à jour `ReportResponseTest.php`**

Remplacer l'import et les instanciations de `ReportTrainerResponse` par `TrainerExternalIdResponse` :

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\CatchStateUsageResponse;
use App\DTO\Response\DexUsageResponse;
use App\DTO\Response\ReportCatchStateResponse;
use App\DTO\Response\ReportDexResponse;
use App\DTO\Response\ReportResponse;
use App\DTO\Response\TrainerExternalIdResponse;
use App\DTO\Response\TrainerCatchStateCountResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ReportResponse::class)]
final class ReportResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $counts = [new TrainerCatchStateCountResponse(
            count: 28,
            trainer: new TrainerExternalIdResponse(externalId: 'abc'),
        )];
        $dexUsage = [new DexUsageResponse(
            count: 2,
            dex: new ReportDexResponse(slug: 'home', name: 'Home', frenchName: 'Home'),
        )];
        $catchStateUsage = [new CatchStateUsageResponse(
            count: 11,
            catchState: new ReportCatchStateResponse(slug: 'no', name: 'No', frenchName: 'Non', color: '#e57373'),
        )];

        $response = new ReportResponse(
            catchStateCountsDefinedByTrainer: $counts,
            dexUsage: $dexUsage,
            catchStateUsage: $catchStateUsage,
        );

        self::assertSame($counts, $response->catchStateCountsDefinedByTrainer);
        self::assertSame($dexUsage, $response->dexUsage);
        self::assertSame($catchStateUsage, $response->catchStateUsage);
    }

    #[Test]
    public function propertiesAreReadonly(): void
    {
        $response = new ReportResponse(
            catchStateCountsDefinedByTrainer: [],
            dexUsage: [],
            catchStateUsage: [],
        );

        self::assertSame([], $response->catchStateCountsDefinedByTrainer);
        self::assertSame([], $response->dexUsage);
        self::assertSame([], $response->catchStateUsage);
    }
}
```

---

### Task 2 : Modifier `ElectionVote` DTO — parsing de `trainer: { external_id }`

**Files:**
- Modify: `src/DTO/ElectionVote.php`
- Modify: `tests/src/Unit/DTO/ElectionVoteTest.php`

- [ ] **Mettre à jour `src/DTO/ElectionVote.php`**

Le OptionsResolver accepte désormais `trainer` (array) au lieu de `trainer_external_id` (string). L'`external_id` est extrait manuellement avec validation de type.

```php
<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\OptionsResolver\OptionsResolver;

final class ElectionVote
{
    public string $trainerExternalId;
    public string $dexSlug;
    public string $electionSlug;

    /**
     * @var string[]
     */
    public array $winnersSlugs;

    /**
     * @var string[]
     */
    public array $losersSlugs;

    /**
     * @param array<string, mixed> $values
     */
    public function __construct(array $values = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        /**
         * @var array{
         *  trainer: array<string, mixed>,
         *  dex_slug: string,
         *  election_slug: string,
         *  winners_slugs: string[],
         *  losers_slugs: string[],
         * } $options
         */
        $options = $resolver->resolve($values);

        if (!isset($options['trainer']['external_id']) || !is_string($options['trainer']['external_id'])) {
            throw new \InvalidArgumentException('trainer.external_id must be a string');
        }

        $this->trainerExternalId = $options['trainer']['external_id'];
        $this->dexSlug = $options['dex_slug'];
        $this->electionSlug = $options['election_slug'];
        $this->winnersSlugs = $options['winners_slugs'];
        $this->losersSlugs = $options['losers_slugs'];
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('trainer');
        $resolver->setAllowedTypes('trainer', 'array');

        $resolver->setDefault('election_slug', '');
        $resolver->setAllowedTypes('election_slug', 'string');

        $resolver->setDefault('dex_slug', '');
        $resolver->setAllowedTypes('dex_slug', 'string');

        $resolver->setRequired('winners_slugs');
        $resolver->setAllowedTypes('winners_slugs', 'string[]');

        $resolver->setRequired('losers_slugs');
        $resolver->setAllowedTypes('losers_slugs', 'string[]');
    }
}
```

- [ ] **Mettre à jour `tests/src/Unit/DTO/ElectionVoteTest.php`**

Toutes les instanciations passent désormais `'trainer' => ['external_id' => '...']` au lieu de `'trainer_external_id' => '...'`.

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\ElectionVote;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

/**
 * @internal
 */
#[CoversClass(ElectionVote::class)]
final class ElectionVoteTest extends TestCase
{
    public function testOk(): void
    {
        $attributes = new ElectionVote([
            'trainer' => ['external_id' => '67865468'],
            'dex_slug' => 'demo',
            'election_slug' => 'douze',
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => ['pichu', 'raichu'],
        ]);

        $this->assertSame('67865468', $attributes->trainerExternalId);
        $this->assertSame('demo', $attributes->dexSlug);
        $this->assertSame('douze', $attributes->electionSlug);
        $this->assertSame(['pikachu'], $attributes->winnersSlugs);
        $this->assertSame(['pichu', 'raichu'], $attributes->losersSlugs);
    }

    public function testMissingElectionSlug(): void
    {
        $attributes = new ElectionVote([
            'trainer' => ['external_id' => '67865468'],
            'dex_slug' => 'demo',
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => ['pichu', 'raichu'],
        ]);

        $this->assertSame('67865468', $attributes->trainerExternalId);
        $this->assertSame('demo', $attributes->dexSlug);
        $this->assertSame('', $attributes->electionSlug);
        $this->assertSame(['pikachu'], $attributes->winnersSlugs);
        $this->assertSame(['pichu', 'raichu'], $attributes->losersSlugs);
    }

    public function testWrongTypeForTrainer(): void
    {
        $this->expectException(InvalidOptionsException::class);

        /**
         * @psalm-suppress InvalidArgument
         *
         * @phpstan-ignore argument.type
         */
        new ElectionVote([
            'trainer' => 'not-an-array',
            'dex_slug' => 'demo',
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => ['pichu', 'raichu'],
        ]);
    }

    public function testWrongTypeForTrainerExternalId(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ElectionVote([
            'trainer' => ['external_id' => 12345],
            'dex_slug' => 'demo',
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => ['pichu', 'raichu'],
        ]);
    }

    public function testWrongValueForElectionSlug(): void
    {
        $this->expectException(InvalidOptionsException::class);

        /**
         * @psalm-suppress InvalidArgument
         *
         * @phpstan-ignore argument.type
         */
        new ElectionVote([
            'trainer' => ['external_id' => '67865468'],
            'dex_slug' => 'demo',
            'election_slug' => false,
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => ['pichu', 'raichu'],
        ]);
    }

    public function testWrongValueForDexSlug(): void
    {
        $this->expectException(InvalidOptionsException::class);

        /**
         * @psalm-suppress InvalidArgument
         *
         * @phpstan-ignore argument.type
         */
        new ElectionVote([
            'trainer' => ['external_id' => '67865468'],
            'dex_slug' => false,
            'election_slug' => 'fav',
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => ['pichu', 'raichu'],
        ]);
    }

    public function testWrongValueForWinnersSlugs(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new ElectionVote([
            'trainer' => ['external_id' => '67865468'],
            'dex_slug' => 'demo',
            'winners_slugs' => 'pikachu',
            'losers_slugs' => ['pichu', 'raichu'],
        ]);
    }

    public function testWrongValueForLosersSlugs(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new ElectionVote([
            'trainer' => ['external_id' => '67865468'],
            'dex_slug' => 'demo',
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => 'pichu',
        ]);
    }

    public function testAnotherValue(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        new ElectionVote([
            'trainer' => ['external_id' => '67865468'],
            'dex_slug' => 'demo',
            'election_slug' => 'douze',
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => ['pichu', 'raichu'],
            'other' => 'idk',
        ]);
    }
}
```

---

### Task 3 : Modifier `ElectionVoteDataResponse` + `ElectionVoteResultResponseFactory`

**Files:**
- Modify: `src/DTO/Response/ElectionVoteDataResponse.php`
- Modify: `src/Factory/ElectionVoteResultResponseFactory.php`
- Modify: `tests/src/Unit/DTO/Response/ElectionVoteDataResponseTest.php`
- Modify: `tests/src/Unit/Factory/ElectionVoteResultResponseFactoryTest.php`

- [ ] **Mettre à jour `ElectionVoteDataResponse.php`**

Remplacer `trainerExternalId: string` par `trainer: TrainerExternalIdResponse` :

```php
<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ElectionVoteDataResponse
{
    /**
     * @param PokemonSlugResponse[] $winners
     * @param PokemonSlugResponse[] $losers
     */
    public function __construct(
        public readonly TrainerExternalIdResponse $trainer,
        public readonly DexSlugResponse $dex,
        #[SerializedName('election_slug')]
        public readonly string $electionSlug,
        public readonly array $winners,
        public readonly array $losers,
    ) {}
}
```

- [ ] **Mettre à jour `ElectionVoteResultResponseFactory.php`**

Ajouter l'import `TrainerExternalIdResponse` et instancier le DTO dans `buildElectionVoteData()` :

```php
<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\ElectionVote;
use App\DTO\ElectionVoteResult;
use App\DTO\PokemonElo;
use App\DTO\Response\DexSlugResponse;
use App\DTO\Response\ElectionVoteDataResponse;
use App\DTO\Response\ElectionVoteResultResponse;
use App\DTO\Response\PokemonEloResponse;
use App\DTO\Response\PokemonsEloResponse;
use App\DTO\Response\PokemonSlugResponse;
use App\DTO\Response\TrainerExternalIdResponse;

final class ElectionVoteResultResponseFactory
{
    public static function fromElectionVoteResult(ElectionVoteResult $result): ElectionVoteResultResponse
    {
        return new ElectionVoteResultResponse(
            electionVote: self::buildElectionVoteData($result->getElectionVote()),
            pokemonsElo: self::buildPokemonsElo($result->getPokemonsElo()),
        );
    }

    private static function buildElectionVoteData(ElectionVote $vote): ElectionVoteDataResponse
    {
        return new ElectionVoteDataResponse(
            trainer: new TrainerExternalIdResponse(externalId: $vote->trainerExternalId),
            dex: new DexSlugResponse(slug: $vote->dexSlug),
            electionSlug: $vote->electionSlug,
            winners: array_map(
                static fn (string $slug): PokemonSlugResponse => new PokemonSlugResponse(slug: $slug),
                $vote->winnersSlugs,
            ),
            losers: array_map(
                static fn (string $slug): PokemonSlugResponse => new PokemonSlugResponse(slug: $slug),
                $vote->losersSlugs,
            ),
        );
    }

    /**
     * @param PokemonElo[][] $pokemonsElo
     */
    private static function buildPokemonsElo(array $pokemonsElo): PokemonsEloResponse
    {
        return new PokemonsEloResponse(
            winners: self::buildPokemonEloList($pokemonsElo['winners'] ?? []),
            losers: self::buildPokemonEloList($pokemonsElo['losers'] ?? []),
        );
    }

    /**
     * @param PokemonElo[] $pokemonElos
     *
     * @return PokemonEloResponse[]
     */
    private static function buildPokemonEloList(array $pokemonElos): array
    {
        return array_map(
            static fn (PokemonElo $pokemonElo): PokemonEloResponse => new PokemonEloResponse(
                pokemon: new PokemonSlugResponse(slug: $pokemonElo->getPokemonSlug()),
                elo: $pokemonElo->getElo(),
            ),
            $pokemonElos,
        );
    }
}
```

- [ ] **Mettre à jour `ElectionVoteDataResponseTest.php`**

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexSlugResponse;
use App\DTO\Response\ElectionVoteDataResponse;
use App\DTO\Response\PokemonSlugResponse;
use App\DTO\Response\TrainerExternalIdResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionVoteDataResponse::class)]
final class ElectionVoteDataResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $trainer = new TrainerExternalIdResponse(externalId: 'abc123');
        $dex = new DexSlugResponse(slug: 'national');
        $winner1 = new PokemonSlugResponse(slug: 'pikachu');
        $winner2 = new PokemonSlugResponse(slug: 'raichu');
        $loser = new PokemonSlugResponse(slug: 'magikarp');

        $response = new ElectionVoteDataResponse(
            trainer: $trainer,
            dex: $dex,
            electionSlug: 'gen1',
            winners: [$winner1, $winner2],
            losers: [$loser],
        );

        self::assertSame($trainer, $response->trainer);
        self::assertSame('abc123', $response->trainer->externalId);
        self::assertSame($dex, $response->dex);
        self::assertSame('national', $response->dex->slug);
        self::assertSame('gen1', $response->electionSlug);
        self::assertSame([$winner1, $winner2], $response->winners);
        self::assertSame('pikachu', $response->winners[0]->slug);
        self::assertSame('raichu', $response->winners[1]->slug);
        self::assertSame([$loser], $response->losers);
        self::assertSame('magikarp', $response->losers[0]->slug);
    }

    #[Test]
    public function constructorAcceptsEmptyPokemonArrays(): void
    {
        $trainer = new TrainerExternalIdResponse(externalId: 'xyz');
        $dex = new DexSlugResponse(slug: '');
        $response = new ElectionVoteDataResponse(
            trainer: $trainer,
            dex: $dex,
            electionSlug: '',
            winners: [],
            losers: [],
        );

        self::assertSame($trainer, $response->trainer);
        self::assertSame('xyz', $response->trainer->externalId);
        self::assertSame($dex, $response->dex);
        self::assertSame([], $response->winners);
        self::assertSame([], $response->losers);
    }
}
```

- [ ] **Mettre à jour `ElectionVoteResultResponseFactoryTest.php`**

Remplacer toutes les assertions sur `->electionVote->trainerExternalId` par `->electionVote->trainer->externalId` :

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\DTO\ElectionVote;
use App\DTO\ElectionVoteResult;
use App\DTO\PokemonElo;
use App\Factory\ElectionVoteResultResponseFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionVoteResultResponseFactory::class)]
final class ElectionVoteResultResponseFactoryTest extends TestCase
{
    #[Test]
    public function fromElectionVoteResultTransformsAllFields(): void
    {
        $electionVote = new ElectionVote([
            'trainer' => ['external_id' => 'trainer42'],
            'dex_slug' => 'national',
            'election_slug' => 'gen1',
            'winners_slugs' => ['pikachu'],
            'losers_slugs' => ['caterpie', 'metapod'],
        ]);
        $pokemonsElo = [
            'winners' => [new PokemonElo('pikachu', 1016)],
            'losers' => [
                new PokemonElo('caterpie', 984),
                new PokemonElo('metapod', 984),
            ],
        ];
        $result = new ElectionVoteResult($electionVote, $pokemonsElo);

        $response = ElectionVoteResultResponseFactory::fromElectionVoteResult($result);

        self::assertSame('trainer42', $response->electionVote->trainer->externalId);
        self::assertSame('national', $response->electionVote->dex->slug);
        self::assertSame('gen1', $response->electionVote->electionSlug);
        self::assertCount(1, $response->electionVote->winners);
        self::assertSame('pikachu', $response->electionVote->winners[0]->slug);
        self::assertCount(2, $response->electionVote->losers);
        self::assertSame('caterpie', $response->electionVote->losers[0]->slug);
        self::assertSame('metapod', $response->electionVote->losers[1]->slug);
        self::assertCount(1, $response->pokemonsElo->winners);
        self::assertSame('pikachu', $response->pokemonsElo->winners[0]->pokemon->slug);
        self::assertSame(1016, $response->pokemonsElo->winners[0]->elo);
        self::assertCount(2, $response->pokemonsElo->losers);
        self::assertSame('caterpie', $response->pokemonsElo->losers[0]->pokemon->slug);
        self::assertSame(984, $response->pokemonsElo->losers[0]->elo);
        self::assertSame('metapod', $response->pokemonsElo->losers[1]->pokemon->slug);
        self::assertSame(984, $response->pokemonsElo->losers[1]->elo);
    }

    #[Test]
    public function fromElectionVoteResultHandlesEmptyPokemonLists(): void
    {
        $electionVote = new ElectionVote([
            'trainer' => ['external_id' => 'trainer1'],
            'dex_slug' => '',
            'election_slug' => '',
            'winners_slugs' => [],
            'losers_slugs' => [],
        ]);
        $result = new ElectionVoteResult($electionVote, ['winners' => [], 'losers' => []]);

        $response = ElectionVoteResultResponseFactory::fromElectionVoteResult($result);

        self::assertSame('trainer1', $response->electionVote->trainer->externalId);
        self::assertSame('', $response->electionVote->dex->slug);
        self::assertSame([], $response->electionVote->winners);
        self::assertSame([], $response->electionVote->losers);
        self::assertSame([], $response->pokemonsElo->winners);
        self::assertSame([], $response->pokemonsElo->losers);
    }

    #[Test]
    public function fromElectionVoteResultHandlesAllWinners(): void
    {
        $electionVote = new ElectionVote([
            'trainer' => ['external_id' => 'trainer1'],
            'dex_slug' => 'demo',
            'election_slug' => '',
            'winners_slugs' => ['caterpie', 'metapod', 'butterfree'],
            'losers_slugs' => [],
        ]);
        $pokemonsElo = [
            'winners' => [
                new PokemonElo('caterpie', 1016),
                new PokemonElo('metapod', 1016),
                new PokemonElo('butterfree', 1016),
            ],
            'losers' => [],
        ];
        $result = new ElectionVoteResult($electionVote, $pokemonsElo);

        $response = ElectionVoteResultResponseFactory::fromElectionVoteResult($result);

        self::assertSame('demo', $response->electionVote->dex->slug);
        self::assertCount(3, $response->electionVote->winners);
        self::assertSame('caterpie', $response->electionVote->winners[0]->slug);
        self::assertSame('metapod', $response->electionVote->winners[1]->slug);
        self::assertSame('butterfree', $response->electionVote->winners[2]->slug);
        self::assertSame([], $response->electionVote->losers);
        self::assertCount(3, $response->pokemonsElo->winners);
        self::assertCount(0, $response->pokemonsElo->losers);
        self::assertSame('caterpie', $response->pokemonsElo->winners[0]->pokemon->slug);
        self::assertSame(1016, $response->pokemonsElo->winners[0]->elo);
        self::assertSame('metapod', $response->pokemonsElo->winners[1]->pokemon->slug);
        self::assertSame('butterfree', $response->pokemonsElo->winners[2]->pokemon->slug);
    }

    #[Test]
    public function fromElectionVoteResultHandlesAllLosers(): void
    {
        $electionVote = new ElectionVote([
            'trainer' => ['external_id' => 'trainer1'],
            'dex_slug' => 'demo',
            'election_slug' => '',
            'winners_slugs' => [],
            'losers_slugs' => ['caterpie', 'metapod', 'butterfree'],
        ]);
        $pokemonsElo = [
            'winners' => [],
            'losers' => [
                new PokemonElo('caterpie', 984),
                new PokemonElo('metapod', 984),
                new PokemonElo('butterfree', 984),
            ],
        ];
        $result = new ElectionVoteResult($electionVote, $pokemonsElo);

        $response = ElectionVoteResultResponseFactory::fromElectionVoteResult($result);

        self::assertSame([], $response->electionVote->winners);
        self::assertCount(3, $response->electionVote->losers);
        self::assertSame('caterpie', $response->electionVote->losers[0]->slug);
        self::assertSame('metapod', $response->electionVote->losers[1]->slug);
        self::assertSame('butterfree', $response->electionVote->losers[2]->slug);
        self::assertCount(0, $response->pokemonsElo->winners);
        self::assertCount(3, $response->pokemonsElo->losers);
        self::assertSame('caterpie', $response->pokemonsElo->losers[0]->pokemon->slug);
        self::assertSame('metapod', $response->pokemonsElo->losers[1]->pokemon->slug);
        self::assertSame('butterfree', $response->pokemonsElo->losers[2]->pokemon->slug);
    }

    #[Test]
    public function fromElectionVoteResultHandlesMissingPokemonEloKeys(): void
    {
        $electionVote = new ElectionVote([
            'trainer' => ['external_id' => 'trainer1'],
            'dex_slug' => 'demo',
            'election_slug' => '',
            'winners_slugs' => [],
            'losers_slugs' => [],
        ]);
        $result = new ElectionVoteResult($electionVote, []);

        $response = ElectionVoteResultResponseFactory::fromElectionVoteResult($result);

        self::assertSame([], $response->pokemonsElo->winners);
        self::assertSame([], $response->pokemonsElo->losers);
        self::assertSame([], $response->electionVote->winners);
        self::assertSame([], $response->electionVote->losers);
    }
}
```

---

### Task 4 : Mettre à jour le test d'intégration

**Files:**
- Modify: `tests/src/Integration/Controller/ElectionVoteControllerTest.php`

- [ ] **Mettre à jour `ElectionVoteControllerTest.php`**

Chaque requête remplace `"trainer_external_id": "..."` par `"trainer": {"external_id": "..."}` dans le body.
Chaque assertion remplace `'trainer_external_id' => '...'` par `'trainer' => ['external_id' => '...']` dans la réponse attendue.

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Controller\ElectionVoteController;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversClass(ElectionVoteController::class)]
final class ElectionVoteControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    public function testVote(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/election/vote',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
            '{"trainer": {"external_id": "12"}, "dex_slug": "demo", "election_slug": "", "winners_slugs": ["butterfree"], "losers_slugs": ["caterpie", "metapod"]}',
        );

        $this->assertResponseStatusCodeSame(200);

        $content = (string) $client->getResponse()->getContent();

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            [
                'election_vote' => [
                    'trainer' => ['external_id' => '12'],
                    'dex' => ['slug' => 'demo'],
                    'election_slug' => '',
                    'winners' => [
                        ['slug' => 'butterfree'],
                    ],
                    'losers' => [
                        ['slug' => 'caterpie'],
                        ['slug' => 'metapod'],
                    ],
                ],
                'pokemons_elo' => [
                    'winners' => [
                        [
                            'pokemon' => ['slug' => 'butterfree'],
                            'elo' => 1016,
                        ],
                    ],
                    'losers' => [
                        [
                            'pokemon' => ['slug' => 'caterpie'],
                            'elo' => 984,
                        ],
                        [
                            'pokemon' => ['slug' => 'metapod'],
                            'elo' => 984,
                        ],
                    ],
                ],
            ],
            $data,
        );
    }

    public function testVoteBis(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/election/vote',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
            '{"trainer": {"external_id": "7b52009b64fd0a2a49e6d8a939753077792b0554"}, "dex_slug": "demo", "election_slug": "", "winners_slugs": ["butterfree"], "losers_slugs": ["caterpie", "metapod"]}',
        );

        $this->assertResponseStatusCodeSame(200);

        $content = (string) $client->getResponse()->getContent();

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            [
                'election_vote' => [
                    'trainer' => ['external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554'],
                    'dex' => ['slug' => 'demo'],
                    'election_slug' => '',
                    'winners' => [
                        ['slug' => 'butterfree'],
                    ],
                    'losers' => [
                        ['slug' => 'caterpie'],
                        ['slug' => 'metapod'],
                    ],
                ],
                'pokemons_elo' => [
                    'winners' => [
                        [
                            'pokemon' => ['slug' => 'butterfree'],
                            'elo' => 1016,
                        ],
                    ],
                    'losers' => [
                        [
                            'pokemon' => ['slug' => 'caterpie'],
                            'elo' => 984,
                        ],
                        [
                            'pokemon' => ['slug' => 'metapod'],
                            'elo' => 984,
                        ],
                    ],
                ],
            ],
            $data,
        );
    }

    public function testVoteAllLosers(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/election/vote',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
            '{"trainer": {"external_id": "7b52009b64fd0a2a49e6d8a939753077792b0554"}, "dex_slug": "demo", "election_slug": "", "winners_slugs": [], "losers_slugs": ["caterpie", "metapod", "butterfree"]}',
        );

        $this->assertResponseStatusCodeSame(200);

        $content = (string) $client->getResponse()->getContent();

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            [
                'election_vote' => [
                    'trainer' => ['external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554'],
                    'dex' => ['slug' => 'demo'],
                    'election_slug' => '',
                    'winners' => [],
                    'losers' => [
                        ['slug' => 'caterpie'],
                        ['slug' => 'metapod'],
                        ['slug' => 'butterfree'],
                    ],
                ],
                'pokemons_elo' => [
                    'winners' => [],
                    'losers' => [
                        [
                            'pokemon' => ['slug' => 'caterpie'],
                            'elo' => 984,
                        ],
                        [
                            'pokemon' => ['slug' => 'metapod'],
                            'elo' => 984,
                        ],
                        [
                            'pokemon' => ['slug' => 'butterfree'],
                            'elo' => 984,
                        ],
                    ],
                ],
            ],
            $data,
        );
    }

    public function testVoteAllWinners(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/election/vote',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
            '{"trainer": {"external_id": "7b52009b64fd0a2a49e6d8a939753077792b0554"}, "dex_slug": "demo", "election_slug": "", "winners_slugs": ["caterpie", "metapod", "butterfree"], "losers_slugs": []}',
        );

        $this->assertResponseStatusCodeSame(200);

        $content = (string) $client->getResponse()->getContent();

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            [
                'election_vote' => [
                    'trainer' => ['external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554'],
                    'dex' => ['slug' => 'demo'],
                    'election_slug' => '',
                    'winners' => [
                        ['slug' => 'caterpie'],
                        ['slug' => 'metapod'],
                        ['slug' => 'butterfree'],
                    ],
                    'losers' => [],
                ],
                'pokemons_elo' => [
                    'winners' => [
                        [
                            'pokemon' => ['slug' => 'caterpie'],
                            'elo' => 1016,
                        ],
                        [
                            'pokemon' => ['slug' => 'metapod'],
                            'elo' => 1016,
                        ],
                        [
                            'pokemon' => ['slug' => 'butterfree'],
                            'elo' => 1016,
                        ],
                    ],
                    'losers' => [],
                ],
            ],
            $data,
        );
    }

    public function testEmptyData(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/election/vote',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
            '',
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testEmptyDataBis(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/election/vote',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
            '{}',
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testBadVote(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/election/vote',
            [],
            [],
            [
                'PHP_AUTH_USER' => AbstractTestControllerApi::AUTH_USER,
                'PHP_AUTH_PW' => AbstractTestControllerApi::AUTH_PASSWORD,
            ],
            '{"trainerExternalId": "12", "dex_slug": "demo", "electionSlug": "", "winnersSlugs": "pichu", "losersSlugs": ["pikachu", "raichu"]}',
        );

        $this->assertResponseStatusCodeSame(400);
    }
}
```
