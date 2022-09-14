<?php

namespace App\Service;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Symfony\Contracts\Cache\CacheInterface;

class SpreadsheetService
{
    private Sheets $service;

    public function __construct(
        protected readonly Client $client,
        private readonly CacheInterface $cache
    ) {
        $this->service = new Sheets($this->client);
    }

    public function get(string $spreadsheetId, string $range): ?ValueRange
    {
        $key = base64_encode("$spreadsheetId-$range");

        /** @var ValueRange|null */
        return $this->cache->get($key, function () use ($spreadsheetId, $range) {
            return $this->service
                ->spreadsheets_values->get($spreadsheetId, $range);
        });
    }
}
