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
### Lumen
1. ``composer require tochka-developers/jsonrpc-doc``
2. Зарегистрируйте сервис-провайдер `Tochka\JsonRpcDoc\ServiceProvider` в `bootstrap/app.php`:
```php
$app->register(Tochka\JsonRpcDoc\ServiceProvider::class);
```
3. Скопируйте конфигурацию из пакета (`vendor/tochka-developers/jsonrpc-doc/config/jsonrpcdoc.php`) в проект (`config/jsonrpcdoc.php`)
4. Подключите конфигурацию в `bootstrap/app.php`:
```php
$app->configure('jsonrpcdoc');
```