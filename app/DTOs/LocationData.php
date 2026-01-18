<?php

namespace App\DTOs;

use App\Enums\LocationSite;

readonly class LocationData
{
    public function __construct(
        public string $code,
        public LocationSite $site,
        public string $name,
        public ?string $description = null,
    ) {}

    /**
     * Convert DTO to array for Eloquent.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'site' => $this->site,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
