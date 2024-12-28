<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
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
        return $user->can('create comments');
    }

    public function update(User $user, Comment $comment): bool
    {
        if ($user->can('edit own comments')) {
            return $user->id == $comment->user_id;
        }
    }

    public function delete(User $user, Comment $comment): bool
    {
        if ($user->can('delete own comments')) {
            return $user->id == $comment->user_id;
        }
    }
}
