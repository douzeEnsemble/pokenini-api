<?php

declare(strict_types=1);

namespace App\DTO\DataChangeReport;

final class Report implements \JsonSerializable
{
    /**
     * @param Statistic[] $detail
     */
    public function __construct(
        public array $detail
    ) {}

    public function merge(Report $report): void
    {
        $this->detail = array_merge($this->detail, $report->detail);
    }

    #[\Override]
    public function jsonSerialize(): mixed
    {
        $data = [];

        foreach ($this->detail as $statistic) {
            $data[$statistic->slug] = $statistic->count;
        }

        return $data;
    }
}
