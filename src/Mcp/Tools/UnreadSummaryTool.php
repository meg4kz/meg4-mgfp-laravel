<?php

namespace Meg4\Mgfp\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Meg4\Mgfp\Mcp\MgfpTool;

#[IsReadOnly(true)]
#[Description('Resumen compacto de mensajes sin leer (web/WhatsApp/Instagram). Payload para que Jarvis anuncie.')]
class UnreadSummaryTool extends MgfpTool
{
    protected string $name = 'unread_summary';

    public function schema(JsonSchema $schema): array
    {
        return ['max_threads' => $schema->integer()->description('Máx. hilos a resumir (1-20).')->default(5)];
    }

    public function handle(Request $request): Response
    {
        $v = $request->validate(['max_threads' => 'sometimes|integer|min:1|max:20']);

        return Response::structured($this->store->unreadSummary(
            $this->storeId($request),
            $v['max_threads'] ?? 5,
        ));
    }
}
