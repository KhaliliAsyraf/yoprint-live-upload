<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'unique_key' => $this->unique_key,
            'product_title' => $this->product_title,
            'product_description' => $this->product_description,
            'style' => $this->style,
            'sanmar_mainframe_color' => $this->sanmar_mainframe_color,
            'size' => $this->size,
            'color_name' => $this->color_name,
            'piece_price' => $this->piece_price,
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
