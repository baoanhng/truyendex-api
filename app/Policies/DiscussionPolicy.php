<?php

namespace App\Policies;

use App\Models\Discussion;
use App\Models\User;

class DiscussionPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Perform pre-authorization checks.
     * Null to check for other authorization methods
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole(['admin', 'moderator'])) {
            return true;
        }

        return null;
    }

    public function create(User $user): bool
    {
        return $user->can('create discussions');
    }

    public function update(User $user, Discussion $discussion): bool
    {
        if ($user->can('edit own discussions')) {
            return $user->id == $discussion->user_id;
        }

        return false;
    }

    public function delete(User $user, Discussion $discussion): bool
    {
        if ($user->can('delete own discussions')) {
            return $user->id == $discussion->user_id;
        }

        return false;
    }
}
