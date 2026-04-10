<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\ActionEnder\ActionEnderTrait;
use App\Message\AbstractActionMessage;
use App\Message\UpdateGamesCollectionsAndDex;
use App\MessageHandler\Traits\CalculateHandlerTrait;
use App\MessageHandler\UpdateGamesCollectionsAndDexHandler;
use App\MessageHandler\UpdateHandlerInterface;
use App\Service\UpdaterService\GamesCollectionsAndDexUpdaterService;
use App\Service\UpdaterService\UpdaterServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * @internal
 */
#[CoversClass(UpdateGamesCollectionsAndDexHandler::class)]
#[UsesClass(GamesCollectionsAndDexUpdaterService::class)]
#[UsesClass(UpdateGamesCollectionsAndDex::class)]
#[CoversTrait(CalculateHandlerTrait::class)]
#[CoversTrait(ActionEnderTrait::class)]
class UpdateGamesCollectionsAndDexHandlerTest extends AbstractTestUpdateHandler
{
    #[\Override]
    public function getServiceClass(): string
    {
        return GamesCollectionsAndDexUpdaterService::class;
    }

    #[\Override]
    public function getHandler(
        UpdaterServiceInterface $updaterService,
        EntityManagerInterface $entityManager,
    ): UpdateHandlerInterface {
        /** @var GamesCollectionsAndDexUpdaterService $updaterService */
        return new UpdateGamesCollectionsAndDexHandler(
            $updaterService,
            $entityManager,
        );
    }

    #[\Override]
    public function getMessage(): AbstractActionMessage
    {
        return new UpdateGamesCollectionsAndDex('12');
    }
}
