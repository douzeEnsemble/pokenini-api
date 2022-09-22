<?php

declare(strict_types=1);

namespace App\Updater;

interface UpdaterInterface
{
    public function execute(?string $sheetName = null): void;
}
