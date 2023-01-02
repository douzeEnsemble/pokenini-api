<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\UpdaterService\RegionalDexNumbersUpdaterService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'app:update:regional_dex_number')]
final class UpdateRegionalDexNumberCommand extends AbstractUpdateCommand
{
    protected static $defaultName = 'app:update:regional_dex_number';

    public function __construct(
        TranslatorInterface $translator,
        RegionalDexNumbersUpdaterService $updaterService,
    ) {
        parent::__construct($translator, $updaterService);
    }
}
