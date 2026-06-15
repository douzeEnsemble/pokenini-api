<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\ElectionPokemonResponse;
use App\DTO\Response\ElectionPokemonsListResponse;
use App\DTO\Response\GameBundlesGroupResponse;
use App\DTO\Response\GameBundleSlugResponse;
use App\DTO\Response\PokemonDataResponse;
use App\DTO\Response\PokemonSlugResponse;
use App\DTO\Response\TypesResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionPokemonsListResponse::class)]
final class ElectionPokemonsListResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $item = $this->buildElectionPokemonResponse();

        $response = new ElectionPokemonsListResponse(
            type: 'pick',
            items: [$item],
        );

        self::assertSame('pick', $response->type);
        self::assertCount(1, $response->items);
        self::assertSame($item, $response->items[0]);
    }

    #[Test]
    public function constructorAcceptsVoteType(): void
    {
        $response = new ElectionPokemonsListResponse(
            type: 'vote',
            items: [],
        );

        self::assertSame('vote', $response->type);
        self::assertSame([], $response->items);
    }

    #[Test]
    public function constructorAcceptsEmptyItems(): void
    {
        $response = new ElectionPokemonsListResponse(
            type: 'pick',
            items: [],
        );

        self::assertCount(0, $response->items);
    }

    private function buildElectionPokemonResponse(): ElectionPokemonResponse
    {
        $pokemon = new PokemonDataResponse(
            slug: 'bulbasaur',
            name: 'Bulbasaur',
            frenchName: 'Bulbizarre',
            nationalDexNumber: 1,
            regionalDexNumber: null,
            simplifiedName: 'Bulbasaur',
            formsLabel: '',
            simplifiedFrenchName: 'Bulbizarre',
            formsFrenchLabel: '',
            icon: 'bulbasaur',
            familyOrder: 0,
            familyLead: new PokemonSlugResponse(slug: 'bulbasaur'),
            originalGameBundle: new GameBundleSlugResponse(slug: 'redgreenblueyellow'),
            orderNumber: '9999-0001-000',
            gameBundles: new GameBundlesGroupResponse(normal: [], shiny: []),
        );

        return new ElectionPokemonResponse(
            pokemon: $pokemon,
            forms: null,
            types: new TypesResponse(primary: null, secondary: null),
        );
    }
}
