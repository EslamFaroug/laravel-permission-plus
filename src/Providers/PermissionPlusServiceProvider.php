<?php

namespace EslamFaroug\PermissionPlus\Providers;

use Illuminate\Support\ServiceProvider;
use EslamFaroug\PermissionPlus\Services\AccessControlManager;

class PermissionPlusServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('accesscontrol', function ($app) {
            return new AccessControlManager();
        });
        // Load helpers
        require_once __DIR__ . '/../Support/helpers.php';
    }

    public function boot()
    {
        // Publish the config file
        $this->publishes([
            __DIR__.'/../config/permission-plus.php' => config_path('permission-plus.php'),
        ], 'permission-plus-config');

        // Publish the migrations
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'permission-plus-migrations');

        // Auto-register Gates for all permissions
        try {
            $table = config('permission-plus.tables.permissions', 'permissions');
            if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                $permissions = \Illuminate\Support\Facades\DB::table($table)->pluck('key');
                foreach ($permissions as $key) {
                    \Illuminate\Support\Facades\Gate::define($key, function ($user) use ($key) {
                        return $user && method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo($key);
                    });
                }
            }
        } catch (\Throwable $e) {
            // Ignore errors during migration or setup
        }
    }
}
