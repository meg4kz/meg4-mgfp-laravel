<?php

namespace Meg4\Mgfp\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Meg4\Mgfp\Mcp\MgfpTool;

#[IsReadOnly(true)]
#[Description('Devuelve un pedido por id, con líneas, totales y estado de pago/envío.')]
class GetOrderTool extends MgfpTool
{
    protected string $name = 'get_order';

    public function schema(JsonSchema $schema): array
    {
        return ['order_id' => $schema->string()->description('Id del pedido.')->required()];
    }

    public function handle(Request $request): Response
    {
        $v = $request->validate(['order_id' => 'required|string']);
        $record = $this->store->getOrder($this->storeId($request), $v['order_id']);

        return $record === null
            ? Response::error('Pedido no encontrado.')
            : Response::structured(['record' => $record]);
    }
}
