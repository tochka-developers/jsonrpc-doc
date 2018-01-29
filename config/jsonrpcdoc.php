<?php

return [
    // Соединение по умолчанию
    'default' => 'api',

    // Список соединений
    'connections' => [
        // Наименование соединения
        'api' => [
            // URL-адрес JsonRpc-сервера
            'url' => 'https://api.jsonrpc.com/v1/jsonrpc',
        ]
    ]
];