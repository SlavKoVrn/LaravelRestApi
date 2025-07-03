<?php

namespace App\Http\Resources;

use App\Helpers\CurrencyHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class GoodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $currency = strtoupper($request->input('currency', 'RUB'));

        $priceRub = $this->rub;

        $rates = CurrencyHelper::exchangeRates();

        switch ($currency) {
            case 'USD':
                $convertedPrice = number_format($priceRub / $rates['USD'], 2, '.', '');
                $formattedPrice = '$' . $convertedPrice;
                break;
            case 'EUR':
                $convertedPrice = number_format($priceRub / $rates['EUR'], 2, '.', '');
                $formattedPrice = '€' . $convertedPrice;
                break;
            case 'RUB':
            default:
                $convertedPrice = number_format($priceRub, 0, '', ' ');
                $formattedPrice = "$convertedPrice ₽";
                break;
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'price' => $formattedPrice,
        ];
    }
}
