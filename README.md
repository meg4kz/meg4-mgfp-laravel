# `meg4/mgfp-laravel` — SDK oficial MGFP para Laravel

Conecta tu tienda (Okastore u otra) a **MEG4 OS (Jarvis)** casi sin código. Tu
app queda como un **MCP server estándar en `/mcp`** (sirve a cualquier asistente)
y MEG4 le pone encima el gobierno (token por-tienda, permisos, confirmación de
escrituras).

> **Instalación:** este paquete está en un **repo git público** (aún no en Packagist); se instala vía **composer VCS**.
> *El SDK es opcional: con la spec (PDF) podés construir tu MCP en cualquier lenguaje.*

## Plug-and-play en 3 pasos

```bash
# 1) declarar el paquete entregado (elige UNA) e instalar
composer config repositories.mgfp vcs https://github.com/meg4kz/meg4-mgfp-laravel.git  # si te damos git
# composer config repositories.mgfp path ./meg4-mgfp-laravel                     # si te damos el .zip
composer require meg4/mgfp-laravel:^1.0 laravel/mcp laravel/sanctum
php artisan mgfp:install        # publica config/rutas e imprime tu service.json

# 2) ver que funciona (datos de demo, sin tu BD)
php artisan mgfp:demo

# 3) verificar conformidad
php artisan mgfp:doctor
```

Eso ya levanta el MCP en `/mcp` con el **núcleo** de tools. Falta solo conectar
tus datos.

## Conectar tus datos (cero código si tu esquema es estándar)

Por defecto usamos **`EloquentAutoStore`**: mapea tus modelos por **config**.
Edita `config/mgfp.php`:

```php
'store_column' => 'store_id',          // columna que scopea cada fila a una tienda
'capabilities' => [                     // qué tools expones (empieza por el núcleo)
    'list_products','get_product','get_stock',
    'unread_summary','list_threads','list_messages','analytics_summary',
    // 'reply_message', 'create_invoice', ...  // opcionales (escritura) cuando quieras
],
'models' => [
    'products' => ['model' => \App\Models\Product::class,
                   'map' => ['id'=>'id','name'=>'name','price'=>'price'],
                   'search' => ['name'], 'stock_column' => 'stock'],
    // customers / orders / invoices …
],
```

¿Esquema especial o lógica propia (inbox, facturación)? Extiende **`BaseStore`**
(trae defaults seguros) y sobrescribe **solo** lo que tengas:

```php
use Meg4\Mgfp\Support\BaseStore;

class OkastoreStore extends BaseStore {
    public function unreadSummary(string $storeId, int $maxThreads): array { /* tu inbox */ }
    public function replyMessage(string $storeId, string $threadId, string $text, string $idem): array {
        // idempotencia por $idem; responde por el mismo canal del hilo (WhatsApp/IG)
    }
}
// .env →  MGFP_STORE=\App\Mgfp\OkastoreStore
```

Lo que no implementes/expongas simplemente **no aparece** en Jarvis (MEG4
autodetecta por `tools/list`).

## Auth: token por tienda (Sanctum) — fácil y seguro

El comercio genera **un token por tienda** (Sanctum, abilities `mgfp:read` /
`mgfp:write`, revocable) y lo **pega una vez** en MEG4. Validar es un guard
(`auth:sanctum`, ya en `routes/ai.php`). Multi-tienda = un token por tienda.

> ¿Quieres click-to-connect con OAuth 2.1? Es opcional (Passport): descomenta el
> bloque OAuth en `routes/ai.php` y pon `"auth": {"type":"oauth2", …}` en tu
> service.json. El default (token) es más simple y igual de seguro.

## Registrarte con MEG4

`mgfp:install` te imprime el `service.json` (con tu `mcp_url = .../mcp`).
Pásanoslo y listo: tus comercios operan todo desde Jarvis por voz.

## Las 15 tools del perfil `commerce` v1

| Tool | Tipo | |
|------|------|--|
| `list_products` `get_product` `get_stock` | lectura · **núcleo** | catálogo/inventario |
| `unread_summary` `list_threads` `list_messages` | lectura · **núcleo** | inbox omnicanal |
| `analytics_summary` | lectura · **núcleo** | ventas |
| `list_customers` `get_customer` | lectura · opcional | clientes |
| `list_orders` `get_order` | lectura · opcional | pedidos |
| `list_invoices` `get_invoice` | lectura · opcional | facturas |
| `create_invoice` | **escritura** · opcional | crear/enviar factura |
| `reply_message` | **escritura** · opcional | responder como en WhatsApp |

Con el **núcleo** (7 lecturas) ya estás en vivo. La especificación completa
(schemas, OAuth, ejemplos, checklist) está en el PDF oficial
`MEG4-OS-MGFP-Integration-Guide.pdf`.
