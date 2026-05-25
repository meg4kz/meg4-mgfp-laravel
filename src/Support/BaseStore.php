<?php

namespace Meg4\Mgfp\Support;

use Meg4\Mgfp\Contracts\StoreRepository;
use RuntimeException;

/**
 * Implementación **progresiva** de {@see StoreRepository}.
 *
 * Trae defaults seguros para TODOS los métodos: las lecturas devuelven vacío y
 * las escrituras lanzan "no soportado". Así implementas **solo lo que tienes**
 * (extiende esta clase y sobrescribe esos métodos). Combinado con
 * `config('mgfp.capabilities')`, solo se exponen en Jarvis las tools que de
 * verdad implementaste.
 */
abstract class BaseStore implements StoreRepository
{
    protected function empty(): array
    {
        return ['records' => [], 'next_cursor' => null];
    }

    protected function unsupported(string $what): never
    {
        throw new RuntimeException("MGFP: '$what' no está soportado por esta tienda. "
            . "Sobrescribe el método en tu StoreRepository o quítalo de config('mgfp.capabilities').");
    }

    // -- lecturas (defaults vacíos) ---------------------------------------- //
    public function listProducts(string $storeId, ?string $filter, ?string $cursor, int $limit): array { return $this->empty(); }
    public function getProduct(string $storeId, string $productId): ?array { return null; }
    public function getStock(string $storeId, string $productId, ?string $variant): array { return ['product_id' => $productId, 'variant' => $variant, 'available' => 0]; }
    public function listCustomers(string $storeId, ?string $filter, ?string $cursor, int $limit): array { return $this->empty(); }
    public function getCustomer(string $storeId, string $customerId): ?array { return null; }
    public function listOrders(string $storeId, ?string $filter, ?string $cursor, int $limit): array { return $this->empty(); }
    public function getOrder(string $storeId, string $orderId): ?array { return null; }
    public function listInvoices(string $storeId, ?string $filter, ?string $cursor, int $limit): array { return $this->empty(); }
    public function getInvoice(string $storeId, string $invoiceId): ?array { return null; }
    public function unreadSummary(string $storeId, int $maxThreads): array { return ['total_unread' => 0, 'top_threads' => [], 'fetched_at' => now()->toIso8601String()]; }
    public function listThreads(string $storeId, string $channel, bool $unreadOnly, ?string $cursor, int $limit): array { return $this->empty(); }
    public function listMessages(string $storeId, string $threadId, int $limit): array { return ['records' => []]; }
    public function analyticsSummary(string $storeId, string $period): array { return ['period' => $period, 'sales_count' => 0, 'revenue' => 0.0, 'top_products' => []]; }

    // -- escrituras (opt-in: lanzan hasta que las implementes) ------------- //
    public function createInvoice(string $storeId, array $params, string $idempotencyKey): array { $this->unsupported('create_invoice'); }
    public function replyMessage(string $storeId, string $threadId, string $text, string $idempotencyKey): array { $this->unsupported('reply_message'); }
}
