<?php

namespace App\Service;

use Google\Client;
use Google\Service\Sheets;

class ImportPokemonsService
{
    public function __construct(private readonly Client $client)
    {
    }

    public function do(): void
    {
        $service = new Sheets($this->client);

        $spreadsheetId = '1VYN1bP66XgYoq2CVycxPbp8P9p4YF_Rw5nk-KDtDGcg';
        $range = 'Resource!L2:P2';
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);

        var_dump($response->getValues());
    }
}
