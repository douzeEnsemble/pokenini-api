<?php

declare(strict_types=1);

namespace App\ActionStarter;

/**
 * Single source of truth of the admin "update" and "calculate" actions.
 *
 * One starter per action, hence the long constructor.
 *
 * @SuppressWarnings("PHPMD.ExcessiveParameterList")
 */
final class ActionCatalog
{
    public const string TYPE_UPDATE = 'update';
    public const string TYPE_CALCULATE = 'calculate';

    /**
     * @var list<ActionDefinition>
     */
    private readonly array $definitions;

    public function __construct(
        UpdateLabelsActionStarter $updateLabels,
        UpdateGamesCollectionsAndDexActionStarter $updateGamesCollectionsAndDex,
        UpdatePokemonsActionStarter $updatePokemons,
        UpdateRegionalDexNumbersActionStarter $updateRegionalDexNumbers,
        UpdateGamesAvailabilitiesActionStarter $updateGamesAvailabilities,
        UpdateGamesShiniesAvailabilitiesActionStarter $updateGamesShiniesAvailabilities,
        UpdateCollectionsAvailabilitiesActionStarter $updateCollectionsAvailabilities,
        CalculateGameBundlesAvailabilitiesActionStarter $calculateGameBundlesAvailabilities,
        CalculateGameBundlesShiniesAvailabilitiesActionStarter $calculateGameBundlesShiniesAvailabilities,
        CalculateDexAvailabilitiesActionStarter $calculateDexAvailabilities,
        CalculatePokemonAvailabilitiesActionStarter $calculatePokemonAvailabilities,
    ) {
        $this->definitions = [
            new ActionDefinition(self::TYPE_UPDATE, 'labels', $updateLabels),
            new ActionDefinition(self::TYPE_UPDATE, 'games_collections_and_dex', $updateGamesCollectionsAndDex),
            new ActionDefinition(self::TYPE_UPDATE, 'pokemons', $updatePokemons),
            new ActionDefinition(self::TYPE_UPDATE, 'regional_dex_numbers', $updateRegionalDexNumbers),
            new ActionDefinition(self::TYPE_UPDATE, 'games_availabilities', $updateGamesAvailabilities),
            new ActionDefinition(self::TYPE_UPDATE, 'games_shinies_availabilities', $updateGamesShiniesAvailabilities),
            new ActionDefinition(self::TYPE_UPDATE, 'collections_availabilities', $updateCollectionsAvailabilities),
            new ActionDefinition(
                self::TYPE_CALCULATE,
                'game_bundles_availabilities',
                $calculateGameBundlesAvailabilities,
                ['update:games_availabilities'],
            ),
            new ActionDefinition(
                self::TYPE_CALCULATE,
                'game_bundles_shinies_availabilities',
                $calculateGameBundlesShiniesAvailabilities,
                ['update:games_shinies_availabilities'],
            ),
            new ActionDefinition(
                self::TYPE_CALCULATE,
                'dex_availabilities',
                $calculateDexAvailabilities,
                ['update:games_availabilities', 'update:collections_availabilities'],
            ),
            new ActionDefinition(
                self::TYPE_CALCULATE,
                'pokemon_availabilities',
                $calculatePokemonAvailabilities,
                [
                    'calculate:game_bundles_availabilities',
                    'calculate:game_bundles_shinies_availabilities',
                    'calculate:dex_availabilities',
                ],
            ),
        ];
    }

    /**
     * @return list<ActionDefinition>
     */
    public function all(): array
    {
        return $this->definitions;
    }

    public function find(string $type, string $name): ?ActionDefinition
    {
        foreach ($this->definitions as $definition) {
            if ($definition->type === $type && $definition->name === $name) {
                return $definition;
            }
        }

        return null;
    }
}
