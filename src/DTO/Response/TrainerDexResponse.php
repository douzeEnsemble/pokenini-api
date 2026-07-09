<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class TrainerDexResponse
{
    public function __construct(
        public readonly DexSlugResponse $dex,
        public readonly TrainerDexSettingsResponse $settings,
        public readonly DexFlagsResponse $flags,
        public readonly AlbumReportResponse $report,
    ) {}
}
