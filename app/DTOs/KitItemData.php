<?php

namespace App\DTOs;

class KitItemData
{
    public function __construct(
        public readonly int $product_id,
        public readonly ?int $location_id = null,
        public readonly int $quantity = 1,
        public readonly ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            product_id: (int) $data['product_id'],
            location_id: isset($data['location_id']) ? (int) $data['location_id'] : null,
            quantity: isset($data['quantity']) ? (int) $data['quantity'] : 1,
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'product_id' => $this->product_id,
            'location_id' => $this->location_id,
            'quantity' => $this->quantity,
            'notes' => $this->notes,
        ];
    }
}
