<?php

namespace App\Enums;

use App\Models\Item;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Validation\ValidationException;

enum ItemType: string implements HasLabel, HasColor
{
    case Consumable = 'consumable';
    case Fixed = 'fixed';
    case Installed = 'installed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Consumable => 'Barang Habis Pakai',
            self::Fixed => 'Aset Tetap',
            self::Installed => 'Aset Terpasang',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Consumable => 'warning',
            self::Fixed => 'success',
            self::Installed => 'info',
        };
    }

    public function getRelationName(): string
    {
        return match ($this) {
            self::Consumable, self::Fixed => 'inventoryItems',
            self::Installed => 'installedInstances',
        };
    }

    public function isConsumable(): bool
    {
        return $this === self::Consumable;
    }

    public function isFixed(): bool
    {
        return $this === self::Fixed;
    }

    public function isInstalled(): bool
    {
        return $this === self::Installed;
    }

    public function validateDeletion(Item $item): void
    {
        $hasDependents = match ($this) {
            self::Consumable => $item->stocks()->sum('quantity') > 0,
            self::Fixed => $item->fixedInstances()->exists(),
            self::Installed => $item->installedInstances()->exists(),
        };

        if ($hasDependents) {
            $msg = match ($this) {
                self::Consumable => 'Masih ada sisa stok fisik.',
                self::Fixed => 'Masih ada unit asset terdaftar.',
                self::Installed => 'Masih ada unit terpasang.',
            };

            throw ValidationException::withMessages(['delete' => "Penghapusan Ditolak: {$msg}"]);
        }
    }
}
