<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\ActionEnder\ActionEnderTrait;
use App\Message\AbstractActionMessage;
use App\Message\UpdatePokemons;
use App\MessageHandler\Traits\CalculateHandlerTrait;
use App\MessageHandler\UpdateHandlerInterface;
use App\MessageHandler\UpdatePokemonsHandler;
use App\Service\UpdaterService\PokemonsUpdaterService;
use App\Service\UpdaterService\UpdaterServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * @internal
 */
#[CoversClass(UpdatePokemonsHandler::class)]
#[UsesClass(PokemonsUpdaterService::class)]
#[UsesClass(UpdatePokemons::class)]
#[CoversTrait(CalculateHandlerTrait::class)]
#[CoversTrait(ActionEnderTrait::class)]
final class UpdatePokemonsHandlerTest extends AbstractTestUpdateHandler
{
    #[\Override]
    public function getServiceClass(): string
    {
        return PokemonsUpdaterService::class;
    }

    #[\Override]
    public function getHandler(
        UpdaterServiceInterface $updaterService,
        EntityManagerInterface $entityManager,
    ): UpdateHandlerInterface {
        /** @var PokemonsUpdaterService $updaterService */
        return new UpdatePokemonsHandler(
            $updaterService,
            $entityManager,
        );
    }

    #[\Override]
    public function getMessage(): AbstractActionMessage
    {
        return new UpdatePokemons('12');
    }
}
