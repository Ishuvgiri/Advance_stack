<?php

namespace App\Providers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;
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
        Gate::define(
            'manage-task',
            static fn (User $user, Task $task): Response => ($user->isAdmin() || $user->id === $task->user_id)
                ? Response::allow()
                : Response::deny('You are not allowed to manage this task.')
        );

        Gate::define('access-admin', static fn (User $user): bool => $user->isAdmin());
    }
}
