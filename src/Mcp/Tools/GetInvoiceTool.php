<?php

namespace Meg4\Mgfp\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Meg4\Mgfp\Mcp\MgfpTool;

#[IsReadOnly(true)]
#[Description('Devuelve una factura por id, con líneas, impuestos y total.')]
class GetInvoiceTool extends MgfpTool
{
    protected string $name = 'get_invoice';

    public function schema(JsonSchema $schema): array
    {
        return ['invoice_id' => $schema->string()->description('Id de la factura.')->required()];
    }

    public function handle(Request $request): Response
    {
        $v = $request->validate(['invoice_id' => 'required|string']);
        $record = $this->store->getInvoice($this->storeId($request), $v['invoice_id']);

        return $record === null
            ? Response::error('Factura no encontrada.')
            : Response::structured(['record' => $record]);
    }
}
