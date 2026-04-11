<?php

declare(strict_types=1);

namespace App\Command;

use App\ActionStarter\UpdateRegionalDexNumbersActionStarter;
use App\Service\UpdaterService\RegionalDexNumbersUpdaterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'app:update:regional_dex_numbers')]
final class   UpdateRegionalDexNumbersCommand extends AbstractUpdateCommand
{
    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        UpdateRegionalDexNumbersActionStarter $actionStarter,
        RegionalDexNumbersUpdaterService $updaterService,
    ) {
        parent::__construct($translator, $entityManager, $actionStarter, $updaterService);
    }

    #[\Override]
    protected function getCommandName(): string
    {
        return 'app:update:regional_dex_numbers';
    }
}
