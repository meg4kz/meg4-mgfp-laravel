<?php

namespace Meg4\Mgfp\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Meg4\Mgfp\Mcp\MgfpTool;

#[IsReadOnly(true)]
#[Description('Stock disponible de un producto (y variante opcional). Para "cuánto me queda de…".')]
class GetStockTool extends MgfpTool
{
    protected string $name = 'get_stock';

    public function schema(JsonSchema $schema): array
    {
        return [
            'product_id' => $schema->string()->description('Id del producto.')->required(),
            'variant' => $schema->string()->description('Variante/talla/color (opcional).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $v = $request->validate([
            'product_id' => 'required|string',
            'variant' => 'sometimes|string',
        ]);

        return Response::structured($this->store->getStock(
            $this->storeId($request),
            $v['product_id'],
            $v['variant'] ?? null,
        ));
    }
}
