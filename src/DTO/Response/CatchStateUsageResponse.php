<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class CatchStateUsageResponse
{
    public function __construct(
        public readonly int $count,
        #[SerializedName('catch_state')]
        public readonly ReportCatchStateResponse $catchState,
    ) {}
}
