<?php

declare(strict_types=1);

namespace App\ActionStarter;

use App\Message\ActionMessageInterface;
use App\Message\UpdateRegionalDexNumbers;

final class UpdateRegionalDexNumbersActionStarter extends AbstractActionStarter
{
    protected function getMessageClass(): string
    {
        return UpdateRegionalDexNumbers::class;
    }

    protected function instanciate(string $identifier): ActionMessageInterface
    {
        return new UpdateRegionalDexNumbers($identifier);
    }
}
