<?php

namespace App\Service;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\CacheInterface;

class SpreadsheetService
{
    private Sheets $service;

    public function __construct(
        protected readonly Client $client,
        private ?CacheInterface $cache = null
    ) {
        $this->service = new Sheets($this->client);

        $this->cache = $cache ?: new FilesystemAdapter();
    }

    public function get(string $spreadsheetId, string $range): ?ValueRange
    {
        $key = base64_encode("$spreadsheetId-$range");

        /** @var ValueRange|null */
        return $this->cache?->get($key, function () use ($spreadsheetId, $range) {
            return $this->service
                ->spreadsheets_values->get($spreadsheetId, $range);
        });
    }

    public function getSheetRowCount(string $spreadsheetId, string $sheetTitle): int
    {
        $key = base64_encode("$spreadsheetId-$sheetTitle-rowCount");

        /** @var int */
        return $this->cache?->get($key, function () use ($spreadsheetId, $sheetTitle) {
            return $this->getSheetGridProperties($spreadsheetId, $sheetTitle)->getRowCount();
        });
    }

    public function getSheetColumnCount(string $spreadsheetId, string $sheetTitle): int
    {
        $key = base64_encode("$spreadsheetId-$sheetTitle-columnCount");

        /** @var int */
        return $this->cache?->get($key, function () use ($spreadsheetId, $sheetTitle) {
            return $this->getSheetGridProperties($spreadsheetId, $sheetTitle)->getColumnCount();
        });
    }

    private function getSheetGridProperties(string $spreadsheetId, string $sheetTitle): Sheets\GridProperties
    {
        $sheets = $this->service->spreadsheets->get($spreadsheetId)->getSheets();

        foreach ($sheets as $sheet) {
            if ($sheet->getProperties()->getTitle() === $sheetTitle) {
                return $sheet->getProperties()->getGridProperties();
            }
        }

        throw new \InvalidArgumentException("Cannot find sheet $sheetTitle in spreadsheet $spreadsheetId");
    }
}
