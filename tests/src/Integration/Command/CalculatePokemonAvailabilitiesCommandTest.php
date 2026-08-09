<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command;

use App\ActionEnder\ActionEnderTrait;
use App\ActionStarter\AbstractActionStarter;
use App\ActionStarter\CalculatePokemonAvailabilitiesActionStarter;
use App\Command\AbstractCalculateCommand;
use App\Command\CalculatePokemonAvailabilitiesCommand;
use App\Message\AbstractActionMessage;
use App\Message\CalculatePokemonAvailabilities;
use App\Repository\PokemonsRepository;
use App\Service\CalculatorService\PokemonAvailabilitiesCalculatorService;
use App\Tests\Common\Traits\CounterTrait\CountActionLogTrait;
use App\Tests\Common\Traits\CounterTrait\CountPokemonAvailabilitiesTrait;
use App\Tests\Common\Traits\CounterTrait\CountPokemonTrait;
use App\Tests\Common\Traits\HasserTrait\HasPokemonAvailabilitiesTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[CoversClass(CalculatePokemonAvailabilitiesCommand::class)]
#[CoversClass(PokemonAvailabilitiesCalculatorService::class)]
#[CoversClass(AbstractCalculateCommand::class)]
#[CoversClass(CalculatePokemonAvailabilitiesActionStarter::class)]
#[CoversClass(AbstractActionStarter::class)]
#[CoversClass(CalculatePokemonAvailabilities::class)]
#[CoversClass(AbstractActionMessage::class)]
#[CoversTrait(ActionEnderTrait::class)]
final class CalculatePokemonAvailabilitiesCommandTest extends AbstractTestCaseCommand
{
    use CountPokemonTrait;
    use CountPokemonAvailabilitiesTrait;
    use HasPokemonAvailabilitiesTrait;
    use CountActionLogTrait;

    #[Test]
    public function noPokemonAvailabilities(): void
    {
        $repo = self::getContainer()->get(PokemonsRepository::class);
        $queryBuilder = $repo->createQueryBuilder('p')
            ->update()
            ->set('p.deletedAt', ':now')
        ;

        /** @psalm-suppress QueryBuilderSetParameter */
        $queryBuilder->setParameter('now', new \DateTimeImmutable());
        $queryBuilder->getQuery()->execute();

        $this->assertEquals(0, $this->getPokemonNotDeletedCount());

        $this->assertEquals(26, $this->getPokemonAvailabilitiesCount('game_bundle'));
        $this->assertEquals(26, $this->getPokemonAvailabilitiesCount('game_bundle_shiny'));

        $initialToProcessCount = $this->getActionLogToProcessCount();
        $initialDoneCount = $this->getActionLogDoneCount();

        $commandTester = $this->executeCommand();
        $commandTester->assertCommandIsSuccessful();

        $this->assertStringContainsString(
            "10 pokemons' availabilities for Game Bundles calculated",
            $commandTester->getDisplay()
        );
        $this->assertStringContainsString(
            "11 pokemons' availabilities for Game Bundles Shiny calculated",
            $commandTester->getDisplay()
        );

        $this->assertEquals(10, $this->getPokemonAvailabilitiesCount('game_bundle'));
        $this->assertEquals(11, $this->getPokemonAvailabilitiesCount('game_bundle_shiny'));

        $this->assertEquals($initialToProcessCount, $this->getActionLogToProcessCount());
        $this->assertEquals($initialDoneCount + 1, $this->getActionLogDoneCount());
    }

    #[Test]
    public function pokemonAvailabilities(): void
    {
        $this->assertEquals(26, $this->getPokemonAvailabilitiesCount('game_bundle'));
        $this->assertEquals(26, $this->getPokemonAvailabilitiesCount('game_bundle_shiny'));

        $initialToProcessCount = $this->getActionLogToProcessCount();
        $initialDoneCount = $this->getActionLogDoneCount();

        $commandTester = $this->executeCommand();
        $commandTester->assertCommandIsSuccessful();

        $this->assertStringContainsString(
            "10 pokemons' availabilities for Game Bundles calculated",
            $commandTester->getDisplay()
        );
        $this->assertStringContainsString(
            "11 pokemons' availabilities for Game Bundles Shiny calculated",
            $commandTester->getDisplay()
        );

        $this->assertEquals(10, $this->getPokemonAvailabilitiesCount('game_bundle'));
        $this->assertEquals(11, $this->getPokemonAvailabilitiesCount('game_bundle_shiny'));

        $this->assertEquals($initialToProcessCount, $this->getActionLogToProcessCount());
        $this->assertEquals($initialDoneCount + 1, $this->getActionLogDoneCount());

        $this->assertTrue($this->hasPokemonAvailabilities('game_bundle', 'bulbasaur'));
        $this->assertTrue($this->hasPokemonAvailabilities('game_bundle_shiny', 'bulbasaur'));
    }

    #[\Override]
    protected function getCommandName(): string
    {
        return 'app:calculate:pokemon_availabilities';
    }
}
