<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class CurrencyHelper
{
    public static function exchangeRates(): array
    {
        return Cache::remember('cbr_currency_rates', now()->addDay(), function () {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://www.cbr.ru/scripts/XML_daily.asp' );
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 300);
            $content = curl_exec($ch);
            curl_close($ch);

            $xml = @simplexml_load_string($content);
            foreach ($xml->Valute as $item) {
                // R01235 - Доллар США
                if ($item['ID'] == 'R01235') {
                    $usd = $item->Value;
                }
                // R01239 - Евро
                if ($item['ID'] == 'R01239') {
                    $eur = $item->Value;
                }
            }
            if (!empty($usd)) {
                $usd = str_replace(',', '.', $usd);
            }
            if (!empty($eur)) {
                $eur = str_replace(',', '.', $eur);
            }
            return [
                'USD' => (float)$usd,
                'EUR' => (float)$eur,
            ];
        });
    }

}