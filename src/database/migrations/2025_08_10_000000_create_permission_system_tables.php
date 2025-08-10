<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableNames = config('permission-plus.tables');
        $columnNames = config('permission-plus.column_names');

        // Permission Guards Table
        Schema::create($tableNames['permission_guards'], function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('name');
            $table->timestamps();
        });

        // Permissions Table
        Schema::create($tableNames['permissions'], function (Blueprint $table) use ($tableNames) {
            $table->id();
            $table->string('key')->unique();
            $table->json('name');
            $table->foreignId('permission_guard_id')->constrained($tableNames['permission_guards'])->cascadeOnDelete();
            $table->timestamps();
        });

        // Roles Table
        Schema::create($tableNames['roles'], function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('name');
            $table->timestamps();
        });

        // Groups Table
        Schema::create($tableNames['groups'], function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('name');
            $table->json('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Role Assignments Table (polymorphic many-to-many)
        Schema::create($tableNames['role_assignments'], function (Blueprint $table) use ($tableNames, $columnNames) {
            $table->id();
            $table->unsignedBigInteger('role_id')->index();
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->timestamps();

            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');
            $table->index(['role_id', $columnNames['model_morph_key'], 'model_type'], 'role_assignments_index');

            $table->foreign('role_id')
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');
        });

        // Permission Assignments Table (polymorphic many-to-many)
        Schema::create($tableNames['permission_assignments'], function (Blueprint $table) use ($tableNames, $columnNames) {
            $table->id();
            $table->unsignedBigInteger('permission_id')->index();
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->timestamps();

            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');
            $table->index(['permission_id', $columnNames['model_morph_key'], 'model_type'], 'permission_assignments_index');

            $table->foreign('permission_id')
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');
        });

        // Groupables Table (polymorphic many-to-many)
        Schema::create($tableNames['groupables'], function (Blueprint $table) use ($tableNames, $columnNames) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->string('groupable_type');
            $table->unsignedBigInteger($columnNames['group_morph_key']);
            $table->timestamps();

            $table->index([$columnNames['group_morph_key'], 'groupable_type'], 'groupables_groupable_id_groupable_type_index');

            $table->foreign('group_id')
                ->references('id')
                ->on($tableNames['groups'])
                ->onDelete('cascade');
                
            // Prevent duplicate group assignments
            $table->unique([
                'group_id',
                'groupable_type',
                $columnNames['group_morph_key']
            ], 'unique_group_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission-plus.tables');
        
        // Drop tables in reverse order of creation to avoid foreign key constraint violations
        Schema::dropIfExists($tableNames['groupables']);
        Schema::dropIfExists($tableNames['permission_assignments']);
        Schema::dropIfExists($tableNames['role_assignments']);
        Schema::dropIfExists($tableNames['groups']);
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);
        Schema::dropIfExists($tableNames['permission_guards']);
    }
};
