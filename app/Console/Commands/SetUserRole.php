<?php

namespace App\Console\Commands;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Console\Command;

class SetUserRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:set-user-role {user? : UserID} {role? : RoleName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign a role to a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user') ?? $this->ask('Enter the user ID');
        $roleName = $this->argument('role') ?? $this->choice('Enter the role name', RolesEnum::values(), default: 2, multiple: false);

        \DB::transaction(function () use ($userId, $roleName) {
            $user = User::find($userId);
            $user->syncRoles($roleName);

            $this->info("Role {$roleName} assigned to user {$user->name}");
        });
    }
}
