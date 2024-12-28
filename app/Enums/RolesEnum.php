<?php

namespace App\Enums;

enum RolesEnum: string
{
    case ADMIN = 'admin';
    case MODERATOR = 'moderator';
    case MEMBER = 'member';

    // Display name
    public function label(): string
    {
        return match ($this) {
            static::ADMIN => 'Admin',
            static::MODERATOR => 'Moderator',
            static::MEMBER => 'Member',
        };
    }
}
