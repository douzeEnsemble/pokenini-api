<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class AlbumReportStatisticResponse
{
    public function __construct(
        #[SerializedName('catch_state')]
        public readonly AlbumCatchStateResponse $catchState,
        public readonly int $count,
    ) {}
}
