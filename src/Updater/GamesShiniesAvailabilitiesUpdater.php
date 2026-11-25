<?php

declare(strict_types=1);

namespace App\Updater;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class GamesShiniesAvailabilitiesUpdater extends GamesAvailabilitiesUpdater
{
    #[\Override]
    protected string $sheetName = 'Games Shinies Availability';
    #[\Override]
    protected string $tableName = 'game_shiny_availability';
    #[\Override]
    protected string $statisticName = 'games_shinies_availabilities';
}
