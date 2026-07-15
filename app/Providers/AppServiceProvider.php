<?php

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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

        // Block definitions live inline in page views:
        //   @block([...schema...]) <template> @endblock
        // The registry parses these from the raw file; if a definition file is
        // ever rendered directly, the bodies are skipped.
        Blade::directive('block', fn () => '<?php if (false): ?>');
        Blade::directive('endblock', fn () => '<?php endif; ?>');
    }

    /**
     * Register application gates.
     *
     * The `cms` gate guards the admin content tools (media library, page
     * builder). It is the app's first real use of `users.user_type`.
     */
    protected function configureAuthorization(): void
    {
        Gate::define('cms', fn (User $user): bool => $user->user_type === 'admin');
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
