<?php

namespace App\Enums;

enum RolesEnum: string
{
    case ADMIN = 'admin';
    case MODERATOR = 'moderator';
    case MEMBER = 'member';
    case EXILE = 'exile';

    // Display name
    public function label(): string
    {
        return match ($this) {
            static::ADMIN => 'Admin',
            static::MODERATOR => 'Moderator',
            static::MEMBER => 'Member',
            static::EXILE => 'Exile',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
