<?php

namespace Meg4\Mgfp\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Meg4\Mgfp\Mcp\MgfpTool;

/**
 * ESCRIBE. Sin `IsReadOnly` ⇒ el Gateway de MEG4 exige scope `:write`,
 * habilitación del dueño y confirmación de voz antes de invocarla.
 * `IsIdempotent` + `idempotency_key` ⇒ reintentar no duplica la factura.
 */
#[IsReadOnly(false)]
#[IsIdempotent(true)]
#[Description('Crea una factura para un pedido (o líneas explícitas) y opcionalmente la envía al cliente.')]
class CreateInvoiceTool extends MgfpTool
{
    protected string $name = 'create_invoice';

    public function schema(JsonSchema $schema): array
    {
        return [
            'order_id' => $schema->string()->description('Id del pedido a facturar (recomendado).'),
            'line_items' => $schema->array()->description('Líneas explícitas si no hay order_id.'),
            'send_to_customer' => $schema->boolean()->description('Enviar la factura al cliente.')->default(true),
            'idempotency_key' => $schema->string()->description('Clave de idempotencia (obligatoria).')->required(),
        ];
    }

    public function handle(Request $request): Response
    {
        $v = $request->validate([
            'order_id' => 'sometimes|string',
            'line_items' => 'sometimes|array',
            'send_to_customer' => 'sometimes|boolean',
            'idempotency_key' => 'required|string',
        ]);

        return Response::structured($this->store->createInvoice(
            $this->storeId($request),
            [
                'order_id' => $v['order_id'] ?? null,
                'line_items' => $v['line_items'] ?? [],
                'send_to_customer' => $v['send_to_customer'] ?? true,
            ],
            $v['idempotency_key'],
        ));
    }
}
