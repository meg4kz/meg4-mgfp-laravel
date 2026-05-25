<?php

namespace Meg4\Mgfp\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Meg4\Mgfp\Mcp\MgfpTool;

#[IsReadOnly(true)]
#[Description('Devuelve un cliente por id, con su historial de compras resumido.')]
class GetCustomerTool extends MgfpTool
{
    protected string $name = 'get_customer';

    public function schema(JsonSchema $schema): array
    {
        return ['customer_id' => $schema->string()->description('Id del cliente.')->required()];
    }

    public function handle(Request $request): Response
    {
        $v = $request->validate(['customer_id' => 'required|string']);
        $record = $this->store->getCustomer($this->storeId($request), $v['customer_id']);

        return $record === null
            ? Response::error('Cliente no encontrado.')
            : Response::structured(['record' => $record]);
    }
}
