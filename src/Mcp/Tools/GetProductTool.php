<?php

namespace Meg4\Mgfp\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Meg4\Mgfp\Mcp\MgfpTool;

#[IsReadOnly(true)]
#[Description('Devuelve un producto por su id, con variantes y precios.')]
class GetProductTool extends MgfpTool
{
    protected string $name = 'get_product';

    public function schema(JsonSchema $schema): array
    {
        return [
            'product_id' => $schema->string()->description('Id del producto.')->required(),
        ];
    }

    public function handle(Request $request): Response
    {
        $v = $request->validate(['product_id' => 'required|string']);
        $record = $this->store->getProduct($this->storeId($request), $v['product_id']);

        return $record === null
            ? Response::error('Producto no encontrado.')
            : Response::structured(['record' => $record]);
    }
}
