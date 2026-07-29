<?php

namespace Tests\Unit\Auth;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserRolePermissionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('role')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_legacy_role_column_still_resolves_admin_access(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isCustomer());
    }

    public function test_spatie_role_assignment_resolves_user_role(): void
    {
        $this->createPermissionTables();

        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $user->assignRole('admin');

        $this->assertTrue($user->fresh()->isAdmin());
    }

    private function createPermissionTables(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['role_id', 'model_id', 'model_type']);
        });
    }
}
