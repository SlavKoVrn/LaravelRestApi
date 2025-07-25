<?php
namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Illuminate\Support\Facades\Log;

class GoogleSheetsService
{
    protected Sheets $service;
    protected string $spreadsheetId;

    public function __construct()
    {
        $client = new Client();
        $client->setApplicationName(config('google.application_name'));
        $client->setScopes(config('google.scopes'));
        $client->setAuthConfig(config('google.credentials_file'));

        $this->service = new Sheets($client);
        $this->spreadsheetId = env('GOOGLE_SHEETS_SPREADSHEET_ID');
    }

    /**
     * Read data from a sheet
     */
    public function readSheet(string $range): array
    {
        try {
            $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
            return $response->getValues() ?: [];
        } catch (\Exception $e) {
            Log::error('Google Sheets Read Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Write data to a sheet (overwrite)
     */
    public function writeSheet(string $range, array $rows): bool
    {
        try {
            $body = new \Google\Service\Sheets\ValueRange([
                'values' => $rows
            ]);

            $this->service->spreadsheets_values->update(
                $this->spreadsheetId,
                $range,
                $body,
                ['valueInputOption' => 'RAW']
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Google Sheets Write Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Append data to a sheet
     */
    public function appendSheet(string $range, array $row): bool
    {
        try {
            $body = new \Google\Service\Sheets\ValueRange([
                'values' => [$row]
            ]);

            $this->service->spreadsheets_values->append(
                $this->spreadsheetId,
                $range,
                $body,
                ['valueInputOption' => 'RAW']
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Google Sheets Append Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
