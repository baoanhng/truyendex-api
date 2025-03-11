<?php

use App\Enums\RolesEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Permission::create(['name' => 'create discussions']);
        Permission::create(['name' => 'edit own discussions']);
        Permission::create(['name' => 'delete own discussions']);
        Permission::create(['name' => 'manage discussions']);

        Role::findByName(RolesEnum::ADMIN->value)->syncPermissions(Permission::all());
        Role::findByName(RolesEnum::MODERATOR->value)->givePermissionTo(['manage discussions']);
        Role::findByName(RolesEnum::MEMBER->value)->givePermissionTo(['create discussions', 'edit own discussions', 'delete own discussions']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
