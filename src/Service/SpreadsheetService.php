<?php

declare(strict_types=1);

namespace App\Service;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;

class SpreadsheetService
{
    private Sheets $service;

    public function __construct(
        protected readonly Client $client,
        string $googleApiSheetsUrl
    ) {
        $this->service = new Sheets($this->client, $googleApiSheetsUrl);
    }

    public function get(string $spreadsheetId, string $range): ?ValueRange
    {
        return $this->service->spreadsheets_values->get($spreadsheetId, $range);
    }

    public function getSheetRowCount(string $spreadsheetId, string $sheetTitle): int
    {
        return $this->getSheetGridProperties($spreadsheetId, $sheetTitle)->getRowCount();
    }

    public function getSheetColumnCount(string $spreadsheetId, string $sheetTitle): int
    {
        return $this->getSheetGridProperties($spreadsheetId, $sheetTitle)->getColumnCount();
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
