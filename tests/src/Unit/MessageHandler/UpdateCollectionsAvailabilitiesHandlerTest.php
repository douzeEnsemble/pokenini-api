<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\ActionEnder\ActionEnderTrait;
use App\Message\AbstractActionMessage;
use App\Message\UpdateCollectionsAvailabilities;
use App\MessageHandler\Traits\CalculateHandlerTrait;
use App\MessageHandler\UpdateCollectionsAvailabilitiesHandler;
use App\MessageHandler\UpdateHandlerInterface;
use App\Service\UpdaterService\CollectionsAvailabilitiesUpdaterService;
use App\Service\UpdaterService\UpdaterServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * @internal
 */
#[CoversClass(UpdateCollectionsAvailabilitiesHandler::class)]
#[UsesClass(CollectionsAvailabilitiesUpdaterService::class)]
#[UsesClass(UpdateCollectionsAvailabilities::class)]
#[CoversTrait(CalculateHandlerTrait::class)]
#[CoversTrait(ActionEnderTrait::class)]
final class UpdateCollectionsAvailabilitiesHandlerTest extends AbstractTestUpdateHandler
{
    #[\Override]
    public function getServiceClass(): string
    {
        return CollectionsAvailabilitiesUpdaterService::class;
    }

    #[\Override]
    public function getHandler(
        UpdaterServiceInterface $updaterService,
        EntityManagerInterface $entityManager,
    ): UpdateHandlerInterface {
        /** @var CollectionsAvailabilitiesUpdaterService $updaterService */
        return new UpdateCollectionsAvailabilitiesHandler(
            $updaterService,
            $entityManager,
        );
    }

    #[\Override]
    public function getMessage(): AbstractActionMessage
    {
        return new UpdateCollectionsAvailabilities('12');
    }
}
