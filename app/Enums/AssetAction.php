<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;

enum AssetAction: string implements HasLabel, HasColor, HasIcon
{
    // SIKLUS HIDUP UTAMA
    case Register = 'register';
    case Update = 'update';
    case Move = 'move';

    case CheckOut = 'check_out';
    case CheckIn = 'check_in';

    // INSTALASI (FIXED)
    case Deploy = 'deploy';
    case Pull = 'pull';

    // KESEHATAN & MAINTENACE
    case ReportBroken = 'report_broken';
    case Maintenance = 'maintenance';
    case Repaired = 'repaired';

    // AKHIR HIDUP
    case MarkAsLost = 'mark_as_lost';
    case Dispose = 'dispose';

    // LAINNYA
    case Audit = 'audit';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Register => 'Registrasi Awal',
            self::Update => 'Update Data',
            self::Move => 'Pindah Lokasi',
            self::CheckOut => 'Peminjaman (Check-Out)',
            self::CheckIn => 'Pengembalian (Check-In)',
            self::Deploy => 'Pemasangan (Deploy)',
            self::Pull => 'Penarikan (Pull)',
            self::ReportBroken => 'Lapor Rusak',
            self::Maintenance => 'Mulai Servis',
            self::Repaired => 'Selesai Servis',
            self::MarkAsLost => 'Dinyatakan Hilang',
            self::Dispose => 'Pemusnahan',
            self::Audit => 'Stock Opname',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Register, self::Repaired, self::CheckIn => 'success',
            self::CheckOut, self::Deploy => 'info',
            self::Update, self::Move, self::Pull => 'gray',
            self::Maintenance, self::Audit => 'warning',
            self::ReportBroken, self::MarkAsLost, self::Dispose => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Register => 'heroicon-m-plus-circle',
            self::Update => 'heroicon-m-pencil-square',
            self::Move => 'heroicon-m-arrows-right-left',
            self::CheckOut => 'heroicon-m-arrow-up-tray',
            self::CheckIn => 'heroicon-m-arrow-down-tray',
            self::Deploy => 'heroicon-m-server',
            self::Pull => 'heroicon-m-arrow-uturn-left',
            self::ReportBroken => 'heroicon-m-exclamation-triangle',
            self::Maintenance => 'heroicon-m-wrench',
            self::Repaired => 'heroicon-m-check-badge',
            self::MarkAsLost => 'heroicon-m-question-mark-circle',
            self::Dispose => 'heroicon-m-trash',
            self::Audit => 'heroicon-m-clipboard-document-check',
        };
    }
}
