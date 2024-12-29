<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'state' => $this->state,
            'totalCost' => $this->totalCost,
            'startDate' => $this->startDate ? $this->startDate->format('Y-m-d') : null,
            'endDate' => $this->endDate ? $this->endDate->format('Y-m-d') : null,
            'requestDate' => $this->requestDate ? $this->requestDate->format('Y-m-d H:i:s') : null,
            'product' => [
                'id' => $this->product->id,
                'title' => $this->product->title,
                'ISBN' => $this->product->ISBN,
                'creator' => $this->product->creator,
                'type' => $this->product->type,
                'rental_price' => $this->product->rental_price,
                'available_stock' => $this->product->available_stock
            ],
            'client' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'rut' => $this->user->rut,
                'email' => $this->user->email,
                'phone' => $this->user->phone
            ],
        ];
    }
}
