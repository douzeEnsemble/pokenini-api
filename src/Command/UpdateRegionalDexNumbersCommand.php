<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\UpdaterService\RegionalDexNumbersUpdaterService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'app:update:regional_dex_numbers')]
final class UpdateRegionalDexNumbersCommand extends AbstractUpdateCommand
{
    protected static $defaultName = 'app:update:regional_dex_numbers';

    public function __construct(
        TranslatorInterface $translator,
        RegionalDexNumbersUpdaterService $updaterService,
    ) {
        parent::__construct($translator, $updaterService);
    }
}
