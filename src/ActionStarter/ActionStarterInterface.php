<?php

declare(strict_types=1);

namespace App\ActionStarter;

use App\Message\ActionMessageInterface;

interface ActionStarterInterface
{
    public function start(): ActionMessageInterface;
}
