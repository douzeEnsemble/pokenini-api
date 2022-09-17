<?php

namespace App\Service\Updater;

interface UpdaterInterface
{
    public function execute(?string $sheetName = null): void;
}
