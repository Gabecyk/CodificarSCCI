<?php

namespace App\Providers;

use App\Repositories\Contracts\TicketRepositoryInterface;
use App\Repositories\Contracts\ResponsibleRepositoryInterface;
use App\Repositories\Eloquent\TicketRepository;
use App\Repositories\Eloquent\ResponsibleRepository;
use App\Services\TicketAssignment\Contracts\AssignmentStrategyInterface;
use App\Services\TicketAssignment\Strategies\LeastBusyAgentStrategy;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TicketRepositoryInterface::class, TicketRepository::class);
        $this->app->bind(ResponsibleRepositoryInterface::class, ResponsibleRepository::class);
        $this->app->bind(AssignmentStrategyInterface::class, LeastBusyAgentStrategy::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
