<?php

declare(strict_types=1);

namespace App\DTO\AlbumReport;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class Report
{
    /**
     * @param Statistic[] $detail
     */
    public function __construct(
        #[SerializedName('total')]
        public int $total,
        #[SerializedName('total_caught')]
        public int $totalCaught,
        #[SerializedName('total_uncaught')]
        public int $totalUncaught,
        #[SerializedName('detail')]
        public array $detail
    ) {}
}
