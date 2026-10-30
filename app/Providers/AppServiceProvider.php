<?php

namespace App\Providers;

use App\Models\Agency;
use App\Models\Contact;
use App\Models\Device;
use App\Models\FlightGroup;
use App\Models\Group;
use App\Models\Meal;
use App\Models\Pilgrim;
use App\Models\Schedule;
use App\Models\User;
use App\Policies\AgencyRecordPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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

        foreach ([FlightGroup::class, Group::class, Pilgrim::class, Schedule::class, Meal::class, Device::class, Contact::class] as $model) {
            Gate::policy($model, AgencyRecordPolicy::class);
        }

        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->getAuthIdentifier() ?: $request->ip()));
        RateLimiter::for('locations', fn (Request $request) => Limit::perMinute(12)->by($request->user()?->getAuthIdentifier() ?: $request->ip()));

        Gate::define('manage-system', fn (User $user): bool => $user->isSystemAdmin());

        Relation::enforceMorphMap([
            'agency' => Agency::class,
            'device' => Device::class,
            'flight_group' => FlightGroup::class,
            'group' => Group::class,
            'pilgrim' => Pilgrim::class,
            'user' => User::class,
        ]);
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
