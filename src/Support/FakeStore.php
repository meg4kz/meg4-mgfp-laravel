<?php

namespace Meg4\Mgfp\Support;

use Meg4\Mgfp\Contracts\StoreRepository;

/**
 * Implementación de DEMO de {@see StoreRepository} con datos fijos.
 *
 * Existe para que el SDK funcione **apenas se instala**, sin tocar tu base de
 * datos: levantas el MCP server y ya responde. Reemplázala por tu propia clase
 * (contra Eloquent) y rebindea el contrato en tu `AppServiceProvider`.
 */
class FakeStore implements StoreRepository
{
    private array $products = [
        ['id' => 'p_botas_negras', 'name' => 'Botas negras', 'price' => 35.0, 'currency' => 'USD',
         'variants' => [['variant' => '38', 'stock' => 3], ['variant' => '39', 'stock' => 0], ['variant' => '40', 'stock' => 5]]],
        ['id' => 'p_chaqueta_roja', 'name' => 'Chaqueta roja', 'price' => 25.0, 'currency' => 'USD',
         'variants' => [['variant' => 'M', 'stock' => 4], ['variant' => 'L', 'stock' => 2]]],
        ['id' => 'p_bolso', 'name' => 'Bolso de cuero', 'price' => 48.0, 'currency' => 'USD',
         'variants' => [['variant' => 'único', 'stock' => 7]]],
    ];

    private array $customers = [
        ['id' => 'c_ana', 'name' => 'Ana Rodríguez', 'orders_count' => 6, 'total_spent' => 210.0],
        ['id' => 'c_pedro', 'name' => 'Pedro Pérez', 'orders_count' => 2, 'total_spent' => 96.0],
    ];

    private array $orders = [
        ['id' => 'o_1043', 'customer_id' => 'c_pedro', 'status' => 'pagado', 'total' => 58.0],
    ];

    private array $invoices = [
        ['id' => 'f_1041', 'order_id' => 'o_1041', 'customer_id' => 'c_ana', 'total' => 35.0, 'status' => 'emitida'],
    ];

    private array $threads = [
        ['thread_id' => 't_maria', 'channel' => 'whatsapp', 'display_name' => 'María', 'unread_count' => 1, 'last_preview' => '¿hay talla M de la chaqueta roja?'],
        ['thread_id' => 't_jose', 'channel' => 'instagram', 'display_name' => 'José', 'unread_count' => 1, 'last_preview' => '¿precio del bolso?'],
        ['thread_id' => 't_luisa', 'channel' => 'whatsapp', 'display_name' => 'Luisa', 'unread_count' => 1, 'last_preview' => '¿envíos a Maracaibo?'],
    ];

    public function listProducts(string $storeId, ?string $filter, ?string $cursor, int $limit): array
    {
        return ['records' => $this->products, 'next_cursor' => null];
    }

    public function getProduct(string $storeId, string $productId): ?array
    {
        return $this->byId($this->products, 'id', $productId);
    }

    public function getStock(string $storeId, string $productId, ?string $variant): array
    {
        $p = $this->byId($this->products, 'id', $productId);
        $available = 0;
        if ($p) {
            foreach ($p['variants'] as $v) {
                if ($variant === null || $v['variant'] === $variant) {
                    $available += $v['stock'];
                }
            }
        }
        return ['product_id' => $productId, 'variant' => $variant, 'available' => $available];
    }

    public function listCustomers(string $storeId, ?string $filter, ?string $cursor, int $limit): array
    {
        return ['records' => $this->customers, 'next_cursor' => null];
    }

    public function getCustomer(string $storeId, string $customerId): ?array
    {
        return $this->byId($this->customers, 'id', $customerId);
    }

    public function listOrders(string $storeId, ?string $filter, ?string $cursor, int $limit): array
    {
        return ['records' => $this->orders, 'next_cursor' => null];
    }

    public function getOrder(string $storeId, string $orderId): ?array
    {
        return $this->byId($this->orders, 'id', $orderId);
    }

    public function listInvoices(string $storeId, ?string $filter, ?string $cursor, int $limit): array
    {
        return ['records' => $this->invoices, 'next_cursor' => null];
    }

    public function getInvoice(string $storeId, string $invoiceId): ?array
    {
        return $this->byId($this->invoices, 'id', $invoiceId);
    }

    public function createInvoice(string $storeId, array $params, string $idempotencyKey): array
    {
        return [
            'record' => ['id' => 'f_1043', 'order_id' => $params['order_id'] ?? '', 'total' => 58.0, 'status' => 'emitida'],
            'sent' => (bool) ($params['send_to_customer'] ?? true),
        ];
    }

    public function unreadSummary(string $storeId, int $maxThreads): array
    {
        return [
            'total_unread' => count($this->threads),
            'top_threads' => array_slice($this->threads, 0, $maxThreads),
            'fetched_at' => now()->toIso8601String(),
        ];
    }

    public function listThreads(string $storeId, string $channel, bool $unreadOnly, ?string $cursor, int $limit): array
    {
        $records = array_values(array_filter(
            $this->threads,
            fn ($t) => $channel === 'any' || $t['channel'] === $channel,
        ));
        return ['records' => $records, 'next_cursor' => null];
    }

    public function listMessages(string $storeId, string $threadId, int $limit): array
    {
        if ($threadId === 't_maria') {
            return ['records' => [[
                'message_id' => 'm1', 'thread_id' => 't_maria', 'direction' => 'inbound',
                'channel' => 'whatsapp', 'body' => 'Hola, ¿hay talla M de la chaqueta roja?',
            ]]];
        }
        return ['records' => []];
    }

    public function replyMessage(string $storeId, string $threadId, string $text, string $idempotencyKey): array
    {
        return ['message_id' => 'm_sent_1', 'thread_id' => $threadId, 'status' => 'sent'];
    }

    public function analyticsSummary(string $storeId, string $period): array
    {
        return [
            'period' => $period, 'sales_count' => 12, 'revenue' => 340.0,
            'top_products' => [['product_id' => 'p_botas_negras', 'units' => 5]],
        ];
    }

    private function byId(array $items, string $key, string $val): ?array
    {
        foreach ($items as $item) {
            if (($item[$key] ?? null) === $val) {
                return $item;
            }
        }
        return null;
    }
}
