<?php

namespace App\DTOs;

class KitData
{
    /**
     * @param KitItemData[] $items
     */
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly bool $is_active = true,
        public readonly array $items = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $items = [];
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $items[] = KitItemData::fromArray($item);
            }
        }

        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            is_active: isset($data['is_active']) ? (bool) $data['is_active'] : true,
            items: $items,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'items' => array_map(fn(KitItemData $item) => $item->toArray(), $this->items),
        ];
    }
}
