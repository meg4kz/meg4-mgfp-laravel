<?php

namespace Meg4\Mgfp\Support;

/**
 * Auto-store por **convención/config**: implementa las lecturas estándar
 * (catálogo, clientes, pedidos, facturas, stock) consultando tus modelos
 * Eloquent según `config('mgfp.models')`, con scoping multi-tienda por
 * `config('mgfp.store_column')`. Para esquemas estándar = **cero código**.
 *
 * Lo que no calce (inbox, analítica a medida, escrituras) lo heredas de
 * {@see BaseStore} (defaults seguros) y lo sobrescribes solo si lo necesitas.
 */
class EloquentAutoStore extends BaseStore
{
    protected function cfg(string $domain): ?array
    {
        return config("mgfp.models.$domain");
    }

    protected function scope($query, string $storeId)
    {
        $col = config('mgfp.store_column');
        if ($col) {
            $query->where($col, $storeId);
        }
        return $query;
    }

    protected function mapRow($row, array $map): array
    {
        $out = [];
        foreach ($map as $field => $col) {
            $out[$field] = $row->{$col} ?? null;
        }
        return $out;
    }

    protected function listDomain(string $domain, string $storeId, ?string $filter, ?string $cursor, int $limit): array
    {
        $cfg = $this->cfg($domain);
        if (!$cfg || empty($cfg['model'])) {
            return $this->empty();
        }
        $model = $cfg['model'];
        $q = $this->scope($model::query(), $storeId);

        if ($filter && !empty($cfg['search'])) {
            $q->where(function ($w) use ($cfg, $filter) {
                foreach ($cfg['search'] as $c) {
                    $w->orWhere($c, 'like', "%{$filter}%");
                }
            });
        }
        if ($cursor) {
            $q->where('id', '>', $cursor);   // cursor simple por clave incremental
        }
        $rows = $q->orderBy('id')->limit($limit + 1)->get();
        $next = $rows->count() > $limit ? (string) $rows[$limit - 1]->id : null;
        $records = $rows->take($limit)->map(fn ($r) => $this->mapRow($r, $cfg['map']))->all();

        return ['records' => $records, 'next_cursor' => $next];
    }

    protected function getDomain(string $domain, string $storeId, string $id): ?array
    {
        $cfg = $this->cfg($domain);
        if (!$cfg || empty($cfg['model'])) {
            return null;
        }
        $model = $cfg['model'];
        $row = $this->scope($model::query(), $storeId)->whereKey($id)->first();
        return $row ? $this->mapRow($row, $cfg['map']) : null;
    }

    // -- catálogo ---------------------------------------------------------- //
    public function listProducts(string $storeId, ?string $filter, ?string $cursor, int $limit): array
    {
        return $this->listDomain('products', $storeId, $filter, $cursor, $limit);
    }

    public function getProduct(string $storeId, string $productId): ?array
    {
        return $this->getDomain('products', $storeId, $productId);
    }

    public function getStock(string $storeId, string $productId, ?string $variant): array
    {
        $cfg = $this->cfg('products');
        $available = 0;
        if ($cfg && !empty($cfg['model'])) {
            $model = $cfg['model'];
            $row = $this->scope($model::query(), $storeId)->whereKey($productId)->first();
            $col = $cfg['stock_column'] ?? 'stock';
            $available = (int) ($row->{$col} ?? 0);
        }
        return ['product_id' => $productId, 'variant' => $variant, 'available' => $available];
    }

    // -- clientes / pedidos / facturas ------------------------------------- //
    public function listCustomers(string $storeId, ?string $filter, ?string $cursor, int $limit): array
    {
        return $this->listDomain('customers', $storeId, $filter, $cursor, $limit);
    }

    public function getCustomer(string $storeId, string $customerId): ?array
    {
        return $this->getDomain('customers', $storeId, $customerId);
    }

    public function listOrders(string $storeId, ?string $filter, ?string $cursor, int $limit): array
    {
        return $this->listDomain('orders', $storeId, $filter, $cursor, $limit);
    }

    public function getOrder(string $storeId, string $orderId): ?array
    {
        return $this->getDomain('orders', $storeId, $orderId);
    }

    public function listInvoices(string $storeId, ?string $filter, ?string $cursor, int $limit): array
    {
        return $this->listDomain('invoices', $storeId, $filter, $cursor, $limit);
    }

    public function getInvoice(string $storeId, string $invoiceId): ?array
    {
        return $this->getDomain('invoices', $storeId, $invoiceId);
    }
}
