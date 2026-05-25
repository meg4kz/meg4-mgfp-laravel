<?php

/**
 * Configuración MGFP — el único archivo que tocas para el modo plug-and-play.
 *
 * Publícalo con:  php artisan vendor:publish --tag=mgfp-config
 */

return [
    // service_id con el que te registras en MEG4 (= serverInfo.name del MCP).
    'service_id' => env('MGFP_SERVICE_ID', 'okastore'),
    'profile' => 'commerce',

    // Implementación de datos. Por defecto el auto-store por convención (Eloquent);
    // cámbialo por tu clase si tu esquema es especial. 'demo' usa FakeStore.
    'store' => env('MGFP_STORE', \Meg4\Mgfp\Support\EloquentAutoStore::class),

    // Columna que scopea cada fila a una tienda (multi-tienda). null = sin scoping.
    'store_column' => env('MGFP_STORE_COLUMN', 'store_id'),

    // Capacidades habilitadas (= tools que se exponen). Empieza por el NÚCLEO y
    // agrega opcionales a medida que las implementas. MEG4 autodetecta por tools/list.
    'capabilities' => [
        // núcleo (lectura) — recomendado para salir en vivo
        'list_products', 'get_product', 'get_stock',
        'unread_summary', 'list_threads', 'list_messages',
        'analytics_summary',
        // opcionales — descomenta cuando las implementes
        // 'list_customers', 'get_customer',
        // 'list_orders', 'get_order',
        // 'list_invoices', 'get_invoice', 'create_invoice',   // create_invoice = escritura
        // 'reply_message',                                     // escritura
    ],

    // Mapeo por convención para EloquentAutoStore (modelo + columnas → campos MGFP).
    // Ajusta los nombres de modelo/columna a tu esquema; lo no mapeado se omite.
    'models' => [
        'products' => [
            'model' => '\\App\\Models\\Product',
            'map' => ['id' => 'id', 'name' => 'name', 'price' => 'price', 'currency' => 'currency'],
            'search' => ['name'],
            'stock_column' => 'stock',
            'variant_relation' => null,        // p.ej. 'variants' (hasMany) si aplica
        ],
        'customers' => [
            'model' => '\\App\\Models\\Customer',
            'map' => ['id' => 'id', 'name' => 'name', 'phone' => 'phone'],
            'search' => ['name', 'phone'],
        ],
        'orders' => [
            'model' => '\\App\\Models\\Order',
            'map' => ['id' => 'id', 'status' => 'status', 'total' => 'total', 'customer_id' => 'customer_id'],
            'search' => ['status'],
        ],
        'invoices' => [
            'model' => '\\App\\Models\\Invoice',
            'map' => ['id' => 'id', 'order_id' => 'order_id', 'total' => 'total', 'status' => 'status'],
            'search' => ['status'],
        ],
    ],
];
