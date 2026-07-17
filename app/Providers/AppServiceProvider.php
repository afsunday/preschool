<?php

namespace App\Providers;

use App\Http\Controllers\SitePageController;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->configureDefaults();
        $this->configureAuthorization();
        $this->configureSiteGlobals();
    }

    /**
     * Bind the site's global blocks to the public layout.
     *
     * A composer rather than a controller prop, so every page — and the page
     * builder's preview — gets the chrome without having to pass it.
     */
    protected function configureSiteGlobals(): void
    {
        View::composer('layouts.site', function ($view) {
            $view->with('globals', SitePageController::globals());
        });
    }

    /**
     * Register application gates.
     *
     * The `cms` gate guards the admin content tools (media library, page
     * builder). It is the app's first real use of `users.user_type`.
     */
    protected function configureAuthorization(): void
    {
        Gate::define('cms', fn (User $user): bool => $user->isAdmin());

        // Portal roles. Classes and children are admin-only to create; teachers
        // run the room they are assigned to.
        Gate::define('portal-admin', fn (User $user): bool => $user->isAdmin());
        Gate::define('portal-staff', fn (User $user): bool => $user->isStaff());
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
