<?php

namespace App\Providers;

use App\Models\StockMovement;
use App\Models\User;
use App\Policies\StockPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(fn (User $user): ?bool => $user->active ? null : false);

        Gate::policy(StockMovement::class, StockPolicy::class);
    }
}
