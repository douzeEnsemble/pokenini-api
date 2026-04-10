<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\ActionEnder\ActionEnderTrait;
use App\Message\AbstractActionMessage;
use App\Message\UpdateGamesShiniesAvailabilities;
use App\MessageHandler\Traits\CalculateHandlerTrait;
use App\MessageHandler\UpdateGamesShiniesAvailabilitiesHandler;
use App\MessageHandler\UpdateHandlerInterface;
use App\Service\UpdaterService\GamesShiniesAvailabilitiesUpdaterService;
use App\Service\UpdaterService\UpdaterServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * @internal
 */
#[CoversClass(UpdateGamesShiniesAvailabilitiesHandler::class)]
#[UsesClass(GamesShiniesAvailabilitiesUpdaterService::class)]
#[UsesClass(UpdateGamesShiniesAvailabilities::class)]
#[CoversTrait(CalculateHandlerTrait::class)]
#[CoversTrait(ActionEnderTrait::class)]
final class UpdateGamesShiniesAvailabilitiesHandlerTest extends AbstractTestUpdateHandler
{
    #[\Override]
    public function getServiceClass(): string
    {
        return GamesShiniesAvailabilitiesUpdaterService::class;
    }

    #[\Override]
    public function getHandler(
        UpdaterServiceInterface $updaterService,
        EntityManagerInterface $entityManager,
    ): UpdateHandlerInterface {
        /** @var GamesShiniesAvailabilitiesUpdaterService $updaterService */
        return new UpdateGamesShiniesAvailabilitiesHandler(
            $updaterService,
            $entityManager,
        );
    }

    #[\Override]
    public function getMessage(): AbstractActionMessage
    {
        return new UpdateGamesShiniesAvailabilities('12');
    }
}
