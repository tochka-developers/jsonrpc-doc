# JSONRPC Doc (Laravel 5.4-5.5, Lumen 5.4-5.5)
## Описание
Генерация документации для JsonRpc-сервера на основе SMD-схемы.
Адаптирован для SMD-схемы, возвращаемой оригинальным модулем Tochka-Developers/JsonRpc версии >1.1.11
## Установка
### Laravel
1. ``composer require tochka-developers/jsonrpc-doc``
2. Добавьте `Tochka\JsonRpcDoc\ServiceProvider` в список сервис-провайдеров в `config/app.php`:
```php
'providers' => [
    //...
    \Tochka\JsonRpcDoc\ServiceProvider::class,
],
```
3. Опубликуйте конфигурацию и ресурсы:  
```
php artisan vendor:publish --tag=config
php artisan vendor:publish --tag=public
```
4. Настройте роутинг для страниц документации (в `App\RouteServiceProvider`):
```php
protected function mapWebRoutes()
{
    // если хотите использовать поддомен (замените SUBDOMAIN на необходимый):
    // важно использовать роутинг с поддоменоном ВЫШЕ роутинга основного домена
    Route::group([
        'domain' => 'SUBDOMAIN.{domain}.{tld}',
        'middleware' => \Tochka\JsonRpcDoc\Middleware\DomainClear::class
    ], function() {
        \Tochka\JsonRpcDoc\ServiceProvider::route();
    });
    
    // если хотите использовать префикс в пути: 
    Route::group([
        'prefix' => 'docs'
    ], function() {
        \Tochka\JsonRpcDoc\ServiceProvider::route();
    });

    Route::middleware('web')
         ->namespace($this->namespace)
         ->group(base_path('routes/web.php'));

}
```
### Lumen
1. ``composer require tochka-developers/jsonrpc-doc``
2. Зарегистрируйте сервис-провайдер `Tochka\JsonRpcDoc\ServiceProvider` в `bootstrap/app.php`:
```php
$app->register(Tochka\JsonRpcDoc\ServiceProvider::class);
```
3. Скопируйте конфигурацию из пакета (`vendor/tochka-developers/jsonrpc-doc/config/jsonrpcdoc.php`) в проект (`config/jsonrpcdoc.php`)
4. Скопируйте ресурсы из пакета (`vendor/tochka-developers/jsonrpc-doc/assets/*`) в проект (`public/vendor/jsonrpcdoc/*`)
5. Подключите конфигурацию в `bootstrap/app.php`:
```php
$app->configure('jsonrpcdoc');
```
6. Настройте роутинг для страниц документации в `bootstrap/app.php`:
```php
// если хотите использовать префикс в пути: 
$app->group([
    'prefix' => 'docs',
], function() {
    \Tochka\JsonRpcDoc\ServiceProvider::route();
});

// если хотите использовать поддомен (замените SUBDOMAIN на необходимый):
$app->routeMiddleware([
    'subdomain' => \Tochka\JsonRpcDoc\Middleware\SubDomain::class,
]);

$app->group([
    'middleware' => 'subdomain:SUBDOMAIN',
], function() {
    \Tochka\JsonRpcDoc\ServiceProvider::route();
});
```
## Настройка
Отредактируйте конфигурацию ``jsonrpcdoc``. Пакет позволяет выводить документацию сразу для нескольких JsonRpc-серверов.
Все используемые сервера должны быть перечислены в списке ``connections`` конфигурации пакета.

Имя используемого по умолчанию соединения должно быть прописано в параметре ``default``. 
Если этот параметр не указан, то в качестве соединения по умолчанию будет использовано первое соединение в списке.

Для использования нескольких документаций для каждой необходимо настроить свою точку входа.
Для этого в роутинге при вызове метода ``\Tochka\JsonRpcDoc\ServiceProvider::route($serviceName)`` 
в качестве ``$serviceName`` должно быть передано имя используемого соединения.
Если имя не передано - будет использовано соединение по умолчанию.

Если пакет ``tochka-developers/jsonrpc-doc`` используется вместе с пакетом ``tochka-developers/jsonrpc``, то в качестве
``url`` в конфигурации можно указать значение ``null``. В таком случае адрес точки входа JsonRpc-сервера будет взят 
из конфигурации пакета ``jsonrpc``. Стоит учесть, что это будет работать только в случае использования автоматического 
роутинга ([Ссылка на раздел документации](https://github.com/tochka-developers/jsonrpc#%D0%90%D0%B2%D1%82%D0%BE%D0%BC%D0%B0%D1%82%D0%B8%D1%87%D0%B5%D1%81%D0%BA%D0%B8%D0%B9-%D1%80%D0%BE%D1%83%D1%82%D0%B8%D0%BD%D0%B3)).
Также, в случае использования нескольких точек входа - будет использована только первая в списке.
> Данная возможность корректно работает только в Laravel. К сожалению, Lumen не поддерживает автоматическое получение 
имени текущего хоста при запуске из консоли. Вы можете самостоятельно устанавливать переменные ``$_SERVER['SERVER_NAME']``
и ``$_SERVER['SERVER_PORT']`` в своем приложении при инициализации, либо прописать имя хоста в ``.env``:
```ini
APP_URL=http://example.org
```

Такое поведение обеспечивает автоматическую работу без дополнительных настроек в большинстве случаев. 
Если же вы наблюдаете ошибку типа ``[ERROR] The host did not return the SMD-scheme. Generating a client is not possible.``,
то попробуйте прописать путь к JsonRpc-серверу в параметре ``url``.

После настройки соединений необходимо получить информацию о сервере (SMD-схему). Для этого выполните команду artisan:
```bash
php artisan jsonrpc:generateDocumentation
```
Если в результате вы увидели сообщение ``[OK] Saving SMD for connection "api" successfull.``, значит все прошло успешно.
Страницы документации после этого должны работать.
Модуль сохраняет схему локально и после этого использует для генерации страниц ее. Поэтому для обновления документации
необходимо снова выполнить команду ``jsonrpc:generateDocumentation``.