<?php

namespace Meg4\Mgfp\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Meg4\Mgfp\Mcp\MgfpTool;

#[IsReadOnly(true)]
#[Description('Lista o busca clientes del comercio. Soporta filtro y paginación.')]
class ListCustomersTool extends MgfpTool
{
    protected string $name = 'list_customers';

    public function schema(JsonSchema $schema): array
    {
        return [
            'filter' => $schema->string()->description('Texto libre de búsqueda (opcional).'),
            'cursor' => $schema->string()->description('Cursor de paginación.'),
            'limit' => $schema->integer()->description('Máximo de registros (1-100).')->default(20),
        ];
    }

    public function handle(Request $request): Response
    {
        $v = $request->validate([
            'filter' => 'sometimes|string',
            'cursor' => 'sometimes|string',
            'limit' => 'sometimes|integer|min:1|max:100',
        ]);

        return Response::structured($this->store->listCustomers(
            $this->storeId($request),
            $v['filter'] ?? null,
            $v['cursor'] ?? null,
            $v['limit'] ?? 20,
        ));
    }
}
