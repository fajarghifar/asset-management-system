<?php

namespace App\Enums;

enum AssetHistoryAction: string
{
    case Checkin = 'checkin';
    case StatusChange = 'status_change';
    case Update = 'update';
    case Movement = 'movement';

    public function getLabel(): string
    {
        return match ($this) {
            self::Checkin => __('history.action.checkin'),
            self::StatusChange => __('history.action.status_change'),
            self::Update => __('history.action.update'),
            self::Movement => __('history.action.movement'),
        };
    }
}
