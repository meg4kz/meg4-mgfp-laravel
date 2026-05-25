<?php

namespace Meg4\Mgfp\Contracts;

/**
 * MGFP — StoreRepository.
 *
 * El ÚNICO contrato que el comercio (p.ej. Okastore) implementa contra sus
 * propios modelos/Eloquent. Las 15 tools canónicas del perfil `commerce`
 * delegan aquí: tú no tocas MCP ni OAuth, solo devuelves TUS datos.
 *
 * Cada método recibe ya validado el input y el **id de la tienda actual**
 * (`storeId`), que el SDK resuelve del token OAuth del request — así una
 * misma instancia sirve a múltiples tiendas (multi-tenant) sin que tu código
 * tenga que pensar en ello.
 *
 * Las tools de ESCRITURA (`replyMessage`, `createInvoice`) reciben una
 * `idempotencyKey`: si ya procesaste esa clave, devuelve el MISMO resultado en
 * vez de duplicar el efecto.
 */
interface StoreRepository
{
    // ---- catálogo --------------------------------------------------------- //

    /** @return array{records: array<int,array>, next_cursor: ?string} */
    public function listProducts(string $storeId, ?string $filter, ?string $cursor, int $limit): array;

    /** @return ?array Producto, o null si no existe. */
    public function getProduct(string $storeId, string $productId): ?array;

    /** @return array{product_id:string, variant:?string, available:int} */
    public function getStock(string $storeId, string $productId, ?string $variant): array;

    // ---- clientes --------------------------------------------------------- //

    /** @return array{records: array<int,array>, next_cursor: ?string} */
    public function listCustomers(string $storeId, ?string $filter, ?string $cursor, int $limit): array;

    public function getCustomer(string $storeId, string $customerId): ?array;

    // ---- pedidos ---------------------------------------------------------- //

    /** @return array{records: array<int,array>, next_cursor: ?string} */
    public function listOrders(string $storeId, ?string $filter, ?string $cursor, int $limit): array;

    public function getOrder(string $storeId, string $orderId): ?array;

    // ---- facturas --------------------------------------------------------- //

    /** @return array{records: array<int,array>, next_cursor: ?string} */
    public function listInvoices(string $storeId, ?string $filter, ?string $cursor, int $limit): array;

    public function getInvoice(string $storeId, string $invoiceId): ?array;

    /**
     * Crea una factura (ESCRIBE). Idempotente por `idempotencyKey`.
     *
     * @param array $params {order_id?:string, line_items?:array, send_to_customer?:bool}
     * @return array{record: array, sent: bool}
     */
    public function createInvoice(string $storeId, array $params, string $idempotencyKey): array;

    // ---- inbox (chats omnicanal) ----------------------------------------- //

    /** @return array{total_unread:int, top_threads: array<int,array>, fetched_at:string} */
    public function unreadSummary(string $storeId, int $maxThreads): array;

    /** @return array{records: array<int,array>, next_cursor: ?string} */
    public function listThreads(string $storeId, string $channel, bool $unreadOnly, ?string $cursor, int $limit): array;

    /** @return array{records: array<int,array>} */
    public function listMessages(string $storeId, string $threadId, int $limit): array;

    /**
     * Responde un hilo por su mismo canal (ESCRIBE). Idempotente por `idempotencyKey`.
     *
     * @return array{message_id:string, thread_id:string, status:string}
     */
    public function replyMessage(string $storeId, string $threadId, string $text, string $idempotencyKey): array;

    // ---- analítica -------------------------------------------------------- //

    /** @return array{period:string, sales_count:int, revenue:float, top_products: array} */
    public function analyticsSummary(string $storeId, string $period): array;
}
