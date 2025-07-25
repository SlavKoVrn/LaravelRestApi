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

    public function getUsedRange(string $sheetName)
    {
            $response = $this->service->spreadsheets->get($this->spreadsheetId, [
                'ranges' => $sheetName,
                'includeGridData' => false
            ]);

            $sheet = collect($response->getSheets())->first(function ($sheet) use ($sheetName) {
                return $sheet->getProperties()->getTitle() === $sheetName;
            });

            if (!$sheet) {
                throw new \Exception("Sheet '$sheetName' not found.");
            }

            $gridProps = $sheet->getProperties()->getGridProperties();
            $rowCount = $gridProps->getRowCount();
            $colCount = $gridProps->getColumnCount();

            // But better: Use the actual data bounds
            // Alternatively, use valueRanges to detect non-empty bounds
            return "{$sheetName}!A1:" . self::columnIndexToLetter($colCount) . $rowCount;
    }

    public static function columnIndexToLetter($columnIndex)
    {
        $letter = '';
        while ($columnIndex > 0) {
            $columnIndex--;
            $letter = chr(65 + ($columnIndex % 26)) . $letter;
            $columnIndex = (int)($columnIndex / 26);
        }
        return $letter;
    }

}
