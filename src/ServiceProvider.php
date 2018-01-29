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

    public static function route($router = null, $serviceName = null)
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

        if ($router !== null) {
            $router->get('{group?}', [
                'uses' => DocumentationController::class . '@index',
                'service_name' => $serviceName
            ])->name('jsonrpcdoc.main');

            $router->get('{group}/{method}', [
                'uses' => DocumentationController::class . '@method',
                'service_name' => $serviceName
            ])->name('jsonrpcdoc.method');
        } else {
            \Route::get('{group?}', [
                'uses' => DocumentationController::class . '@index',
                'service_name' => $serviceName
            ])->name('jsonrpcdoc.main');

            \Route::get('{group}/{method}', [
                'uses' => DocumentationController::class . '@method',
                'service_name' => $serviceName
            ])->name('jsonrpcdoc.method');
        }
    }
}