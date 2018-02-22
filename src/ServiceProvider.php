<?php

namespace Tochka\JsonRpcDoc;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                DocumentationGenerator::class
            ]);
        }
        
        $this->publishes([
            __DIR__ . '/../config/jsonrpcdoc.php' => base_path('config/jsonrpcdoc.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../assets' => public_path('vendor/jsonrpcdoc'),
        ], 'public');

        $this->loadViewsFrom(__DIR__ . '/views', 'jsonrpcdoc');

    }

    public function register()
    {
        // TODO: Implement register() method.
    }

    public static function route($serviceName = null)
    {
        if (empty($serviceName)) {
            $serviceName = config('jsonrpcdoc.default');
        }

        if (empty($serviceName)) {
            $serviceName = array_first(array_keys(config('jsonrpcdoc.connections', [])));
        }

        if (empty($serviceName)) {
            throw new \RuntimeException('Не указан сервис, для которого необходимо отображать документацию');
        }

        if (is_lumen()) {
            self::routeForLumen($serviceName);
        } else {
            self::routeForLaravel($serviceName);
        }
    }

    protected static function routeForLumen($serviceName = null)
    {
        $router = app();

        if (version_compare(getVersion(), '5.5', '>=')) {
            $router = $app->router;
        }

        $router->get('', [
            'as' => 'jsonrpcdoc.main',
            'uses' => LumenController::class . '@index',
            'service_name' => $serviceName
        ]);

        $router->get('{group}', [
            'as' => 'jsonrpcdoc.group',
            'uses' => LumenController::class . '@index',
            'service_name' => $serviceName
        ]);

        $router->get('{group}/{method}', [
            'as' => 'jsonrpcdoc.method',
            'uses' => LumenController::class . '@method',
            'service_name' => $serviceName
        ]);
    }

    protected static function routeForLaravel($serviceName = null)
    {
        \Route::get('', [
            'uses' => LaravelController::class . '@index',
            'service_name' => $serviceName
        ])->name('jsonrpcdoc.main');

        \Route::get('{group}', [
            'uses' => LaravelController::class . '@index',
            'service_name' => $serviceName
        ])->name('jsonrpcdoc.group');

        \Route::get('{group}/{method}', [
            'uses' => DocumentationController::class . '@method',
            'service_name' => $serviceName
        ])->name('jsonrpcdoc.method');
    }
}