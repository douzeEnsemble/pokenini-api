<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class ElectionReportResponse
{
    /**
     * @param ElectionEloResponse[] $top
     *
     * The constructor's closing line is a known false negative in Xdebug/php-code-coverage
     * for constructors composed entirely of promoted properties (see
     * https://github.com/sebastianbergmann/php-code-coverage/issues/843 and
     * https://bugs.xdebug.org/view.php?id=1910): it is exercised by
     * ElectionReportResponseFactoryTest and ElectionReportControllerTest, but the coverage
     * driver never records the hit.
     *
     * @codeCoverageIgnore
     */
    public function __construct(
        public readonly array $top,
        public readonly ElectionMetricsResponse $metrics,
    ) {}
}
