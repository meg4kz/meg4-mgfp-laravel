<?php

namespace Meg4\Mgfp\Mcp;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Meg4\Mgfp\Mcp\Tools\AnalyticsSummaryTool;
use Meg4\Mgfp\Mcp\Tools\CreateInvoiceTool;
use Meg4\Mgfp\Mcp\Tools\GetCustomerTool;
use Meg4\Mgfp\Mcp\Tools\GetInvoiceTool;
use Meg4\Mgfp\Mcp\Tools\GetOrderTool;
use Meg4\Mgfp\Mcp\Tools\GetProductTool;
use Meg4\Mgfp\Mcp\Tools\GetStockTool;
use Meg4\Mgfp\Mcp\Tools\ListCustomersTool;
use Meg4\Mgfp\Mcp\Tools\ListInvoicesTool;
use Meg4\Mgfp\Mcp\Tools\ListMessagesTool;
use Meg4\Mgfp\Mcp\Tools\ListOrdersTool;
use Meg4\Mgfp\Mcp\Tools\ListProductsTool;
use Meg4\Mgfp\Mcp\Tools\ListThreadsTool;
use Meg4\Mgfp\Mcp\Tools\ReplyMessageTool;
use Meg4\Mgfp\Mcp\Tools\UnreadSummaryTool;

/**
 * MGFP — servidor MCP del perfil `commerce` v1.
 *
 * Conformidad MGFP: el `serverInfo.name` es el `service_id` con el que te
 * registras en MEG4 (aquí "okastore"), y la versión declara el perfil. MEG4
 * descubre las 15 tools canónicas vía `tools/list` y deriva los scopes RBAC de
 * las anotaciones `IsReadOnly`/`IsDestructive`/`IsIdempotent` de cada tool.
 *
 * Cambia `#[Name(...)]` por tu propio `service_id` si no eres Okastore.
 */
#[Name('okastore')]
#[Version('1.0.0')]
#[Instructions(
    'MGFP/1.0 perfil commerce. Tienda digital federada a MEG4 OS (Jarvis). '.
    'Las tools de escritura (create_invoice, reply_message) NO son read-only y '.
    'requieren idempotency_key. Multi-tienda: cada token OAuth scopea a una tienda.'
)]
class CommerceServer extends Server
{
    /** Las 15 tools canónicas del perfil commerce v1. */
    protected array $tools = [
        // catálogo + inventario
        ListProductsTool::class,
        GetProductTool::class,
        GetStockTool::class,
        // clientes
        ListCustomersTool::class,
        GetCustomerTool::class,
        // pedidos
        ListOrdersTool::class,
        GetOrderTool::class,
        // facturas
        ListInvoicesTool::class,
        GetInvoiceTool::class,
        CreateInvoiceTool::class,        // ✍️ write
        // inbox omnicanal
        UnreadSummaryTool::class,
        ListThreadsTool::class,
        ListMessagesTool::class,
        ReplyMessageTool::class,         // ✍️ write
        // analítica
        AnalyticsSummaryTool::class,
    ];

    protected array $resources = [];
    protected array $prompts = [];
}
