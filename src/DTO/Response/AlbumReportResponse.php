<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class AlbumReportResponse
{
    /**
     * @param AlbumReportStatisticResponse[] $detail
     */
    public function __construct(
        public readonly int $total,
        #[SerializedName('total_caught')]
        public readonly int $totalCaught,
        #[SerializedName('total_uncaught')]
        public readonly int $totalUncaught,
        public readonly array $detail,
    ) {}
}
