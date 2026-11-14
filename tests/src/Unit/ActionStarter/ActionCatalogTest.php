<?php

declare(strict_types=1);

namespace App\Tests\Unit\ActionStarter;

use App\ActionStarter\ActionCatalog;
use App\ActionStarter\ActionDefinition;
use App\ActionStarter\ActionStarterInterface;
use App\ActionStarter\CalculateDexAvailabilitiesActionStarter;
use App\ActionStarter\CalculateGameBundlesAvailabilitiesActionStarter;
use App\ActionStarter\CalculateGameBundlesShiniesAvailabilitiesActionStarter;
use App\ActionStarter\CalculatePokemonAvailabilitiesActionStarter;
use App\ActionStarter\UpdateCollectionsAvailabilitiesActionStarter;
use App\ActionStarter\UpdateGamesAvailabilitiesActionStarter;
use App\ActionStarter\UpdateGamesCollectionsAndDexActionStarter;
use App\ActionStarter\UpdateGamesShiniesAvailabilitiesActionStarter;
use App\ActionStarter\UpdateLabelsActionStarter;
use App\ActionStarter\UpdatePokemonsActionStarter;
use App\ActionStarter\UpdateRegionalDexNumbersActionStarter;
use App\Repository\ActionLogsRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
#[CoversClass(ActionCatalog::class)]
#[CoversClass(ActionDefinition::class)]
final class ActionCatalogTest extends TestCase
{
    #[Test]
    public function allDefinitionsAreUniqueAndDependenciesExist(): void
    {
        $catalog = $this->catalog();

        $keys = array_map(
            static fn (ActionDefinition $definition): string => "{$definition->type}:{$definition->name}",
            $catalog->all(),
        );

        $this->assertCount(11, $keys);
        $this->assertSame($keys, array_values(array_unique($keys)));

        foreach ($catalog->all() as $definition) {
            foreach ($definition->dependsOn as $dependency) {
                $this->assertContains($dependency, $keys);
            }
        }
    }

    #[Test]
    public function findReturnsTheMatchingDefinition(): void
    {
        $catalog = $this->catalog();

        $definition = $catalog->find('calculate', 'dex_availabilities');

        $this->assertNotNull($definition);
        $this->assertSame('dex_availabilities', $definition->name);
        $this->assertInstanceOf(CalculateDexAvailabilitiesActionStarter::class, $definition->starter);
    }

    #[Test]
    public function findDistinguishesTypes(): void
    {
        $catalog = $this->catalog();

        $this->assertNull($catalog->find('calculate', 'labels'));
        $this->assertNull($catalog->find('update', 'dex_availabilities'));
        $this->assertNull($catalog->find('update', 'unknown'));
    }

    #[Test]
    public function definitionIsSerializedWithoutStarter(): void
    {
        $definition = new ActionDefinition('update', 'labels', $this->createStub(ActionStarterInterface::class), ['a:b']);

        $this->assertSame(
            ['type' => 'update', 'name' => 'labels', 'depends_on' => ['a:b']],
            $definition->jsonSerialize(),
        );
    }

    private function catalog(): ActionCatalog
    {
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $repo = $this->createStub(ActionLogsRepository::class);

        return new ActionCatalog(
            new UpdateLabelsActionStarter($entityManager, $repo),
            new UpdateGamesCollectionsAndDexActionStarter($entityManager, $repo),
            new UpdatePokemonsActionStarter($entityManager, $repo),
            new UpdateRegionalDexNumbersActionStarter($entityManager, $repo),
            new UpdateGamesAvailabilitiesActionStarter($entityManager, $repo),
            new UpdateGamesShiniesAvailabilitiesActionStarter($entityManager, $repo),
            new UpdateCollectionsAvailabilitiesActionStarter($entityManager, $repo),
            new CalculateGameBundlesAvailabilitiesActionStarter($entityManager, $repo),
            new CalculateGameBundlesShiniesAvailabilitiesActionStarter($entityManager, $repo),
            new CalculateDexAvailabilitiesActionStarter($entityManager, $repo),
            new CalculatePokemonAvailabilitiesActionStarter($entityManager, $repo),
        );
    }
}
