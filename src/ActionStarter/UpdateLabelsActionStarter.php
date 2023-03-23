<?php

declare(strict_types=1);

namespace App\ActionStarter;

use App\Message\ActionMessageInterface;
use App\Message\UpdateLabels;

final class UpdateLabelsActionStarter extends AbstractActionStarter
{
    protected function getMessageClass(): string
    {
        return UpdateLabels::class;
    }

    protected function instanciate(string $identifier): ActionMessageInterface
    {
        return new UpdateLabels($identifier);
    }
}
