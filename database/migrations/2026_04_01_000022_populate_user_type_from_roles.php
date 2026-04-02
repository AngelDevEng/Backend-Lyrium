<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $adminRole = Role::where('name', 'administrator')->first();
        $sellerRole = Role::where('name', 'seller')->first();
        $logisticsRole = Role::where('name', 'logistics_operator')->first();
        $customerRole = Role::where('name', 'customer')->first();

        if ($adminRole) {
            DB::table('users')
                ->whereExists(function ($query) use ($adminRole) {
                    $query->select(DB::raw(1))
                        ->from('model_has_roles')
                        ->whereColumn('model_has_roles.model_id', 'users.id')
                        ->where('model_has_roles.role_id', $adminRole->id);
                })
                ->update(['user_type' => 'admin']);
        }

        if ($sellerRole) {
            DB::table('users')
                ->whereExists(function ($query) use ($sellerRole) {
                    $query->select(DB::raw(1))
                        ->from('model_has_roles')
                        ->whereColumn('model_has_roles.model_id', 'users.id')
                        ->where('model_has_roles.role_id', $sellerRole->id);
                })
                ->update(['user_type' => 'seller']);
        }

        if ($logisticsRole) {
            DB::table('users')
                ->whereExists(function ($query) use ($logisticsRole) {
                    $query->select(DB::raw(1))
                        ->from('model_has_roles')
                        ->whereColumn('model_has_roles.model_id', 'users.id')
                        ->where('model_has_roles.role_id', $logisticsRole->id);
                })
                ->update(['user_type' => 'delivery']);
        }

        if ($customerRole) {
            DB::table('users')
                ->whereExists(function ($query) use ($customerRole) {
                    $query->select(DB::raw(1))
                        ->from('model_has_roles')
                        ->whereColumn('model_has_roles.model_id', 'users.id')
                        ->where('model_has_roles.role_id', $customerRole->id);
                })
                ->update(['user_type' => 'customer']);
        }
    }

    public function down(): void
    {
        DB::table('users')->update(['user_type' => null]);
    }
};
