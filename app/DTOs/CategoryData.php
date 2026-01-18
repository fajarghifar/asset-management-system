<?php

namespace App\DTOs;

readonly class CategoryData
{
    public function __construct(
        public string $name,
        public ?string $description = null,
    ) {}
}
