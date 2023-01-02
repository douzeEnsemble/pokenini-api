<?php

namespace App\Command;

use App\Service\UpdaterService\LabelsUpdaterService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'app:update:labels')]
final class UpdateLabelsCommand extends AbstractUpdateCommand
{
    protected static $defaultName = 'app:update:labels';

    public function __construct(
        TranslatorInterface $translator,
        LabelsUpdaterService $updaterService,
    ) {
        parent::__construct($translator, $updaterService);
    }
}
