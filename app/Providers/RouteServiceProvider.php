<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
  /**
   * The path to the "home" route for your application.
   *
   * This is used by Laravel authentication to redirect users after login.
   *
   * @var string
   */
  public const HOME = '/dashboard';

  /**
   * The controller namespace for the application.
   *
   * When present, controller route declarations will automatically be prefixed with this namespace.
   *
   * @var string|null
   */
  // protected $namespace = 'App\\Http\\Controllers';

  /**
   * Define your route model bindings, pattern filters, etc.
   *
   * @return void
   */
  public function boot()
  {
    $this->configureRateLimiting();

    $this->routes(function () {
      Route::prefix('api')
        ->middleware('api')
        ->middleware('web')
        ->namespace($this->namespace)
        ->group(base_path('routes/api.php'));

      Route::middleware('web')
        ->namespace($this->namespace)
        ->group(base_path('routes/web.php'));

      Route::middleware('web')
        ->namespace($this->namespace)
        ->prefix('customer')
        ->name('customer.')
        ->group(base_path('routes/customer.php'));

      Route::middleware('web')
        ->namespace($this->namespace)
        ->prefix('coach')
        ->name('coach.')
        ->group(base_path('routes/coach.php'));

      Route::middleware('web')
        ->namespace($this->namespace)
        ->group(base_path('routes/prospect.php'));

      Route::middleware('web')
        ->namespace($this->namespace)
        ->prefix('admin')
        ->group(base_path('routes/admin.php'));

      Route::prefix('tests')
        ->middleware('web')
        ->name('tests.')
        ->namespace($this->namespace)
        ->group(base_path('routes/test.php'));

      Route::middleware('web')
        ->namespace($this->namespace)
        ->prefix('intervenant')
        ->name('intervenant.')
        ->group(base_path('routes/intervenant.php'));

      Route::middleware('web')
        ->namespace($this->namespace)
        ->prefix('post')
        ->name('post.')
        ->group(base_path('routes/post.php'));

      Route::middleware('web')
        ->namespace($this->namespace)
        ->prefix('folowed')
        ->name('folowed.')
        ->group(base_path('routes/folowed.php'));
    });
  }

  /**
   * Configure the rate limiters for the application.
   *
   * @return void
   */
  protected function configureRateLimiting()
  {
    RateLimiter::for('api', function (Request $request) {
      return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
    });
  }
}
