<?php

namespace App\Http\Resources;

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

        switch ($currency) {
            case 'USD':
                $convertedPrice = number_format($priceRub / 90, 2, '.', '');
                $formattedPrice = '$' . $convertedPrice;
                break;
            case 'EUR':
                $convertedPrice = number_format($priceRub / 100, 2, '.', '');
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
