<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\UpdaterService\PokemonsUpdaterService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'app:update:pokemons')]
final class UpdatePokemonsCommand extends AbstractUpdateCommand
{
    protected static $defaultName = 'app:update:pokemons';

    public function __construct(
        TranslatorInterface $translator,
        PokemonsUpdaterService $updaterService,
    ) {
        parent::__construct($translator, $updaterService);
    }
}
