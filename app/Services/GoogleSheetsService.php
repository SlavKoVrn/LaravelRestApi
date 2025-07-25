<?php
namespace App\Services;

use Google\Client;
use GuzzleHttp\Client as GuzzleClient;
use Google\Service\Sheets;

class GoogleSheetsService
{
    protected Sheets $service;
    protected string $spreadsheetId;

    public function __construct($credentials, $spreadsheetId)
    {
        $client = new Client();
        $client->setApplicationName(config('google.application_name'));
        $client->setScopes(config('google.scopes'));
        $client->setAuthConfig($credentials);
        $guzzleClient = new GuzzleClient([
            'verify' => base_path('cacert.pem'),
        ]);
        $client->setHttpClient($guzzleClient);

        $this->service = new Sheets($client);
        $this->spreadsheetId = $spreadsheetId;
    }

    /**
     * Read data from a sheet
     */
    public function readSheet(string $range): array
    {
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
        return $response->getValues() ?: [];
    }

    /**
     * Write data to a sheet (overwrite)
     */
    public function writeSheet(string $range, array $rows)
    {
        $body = new \Google\Service\Sheets\ValueRange([
            'values' => $rows
        ]);

        $this->service->spreadsheets_values->update(
            $this->spreadsheetId,
            $range,
            $body,
            ['valueInputOption' => 'RAW']
        );
    }

    /**
     * Append data to a sheet
     */
    public function appendSheet(string $range, array $row)
    {
        $body = new \Google\Service\Sheets\ValueRange([
            'values' => [$row]
        ]);

        $this->service->spreadsheets_values->append(
            $this->spreadsheetId,
            $range,
            $body,
            ['valueInputOption' => 'RAW']
        );
    }
}
