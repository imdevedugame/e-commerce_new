<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'brief_description' => $this->brief_description,
            'description' => $this->description,
            'price' => (float) $this->price,
            'old_price' => (float) $this->old_price,
            'sku' => $this->SKU,
            'stock_status' => $this->stock_status,
            'quantity' => $this->quantity,
            'image' => $this->image,
            'images' => json_decode($this->images, true) ?: [],
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
