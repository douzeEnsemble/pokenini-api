<?php

declare(strict_types=1);

namespace App\Command;

use App\ActionStarter\UpdateLabelsActionStarter;
use App\Service\UpdaterService\LabelsUpdaterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'app:update:labels')]
final class UpdateLabelsCommand extends AbstractUpdateCommand
{
    protected static $defaultName = 'app:update:labels';

    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        UpdateLabelsActionStarter $actionStarter,
        LabelsUpdaterService $updaterService,
    ) {
        parent::__construct($translator, $entityManager, $actionStarter, $updaterService);
    }
}
