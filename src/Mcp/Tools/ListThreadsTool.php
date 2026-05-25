<?php

namespace Meg4\Mgfp\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Meg4\Mgfp\Mcp\MgfpTool;

#[IsReadOnly(true)]
#[Description('Lista hilos de conversación (contactos/grupos). Filtro por canal y solo-no-leídos.')]
class ListThreadsTool extends MgfpTool
{
    protected string $name = 'list_threads';

    public function schema(JsonSchema $schema): array
    {
        return [
            'channel' => $schema->string()->enum(['web', 'whatsapp', 'instagram', 'any'])
                ->description('Canal a filtrar.')->default('any'),
            'unread_only' => $schema->boolean()->description('Solo conversaciones sin leer.')->default(false),
            'cursor' => $schema->string()->description('Cursor de paginación.'),
            'limit' => $schema->integer()->description('Máximo de registros (1-100).')->default(20),
        ];
    }

    public function handle(Request $request): Response
    {
        $v = $request->validate([
            'channel' => 'sometimes|string|in:web,whatsapp,instagram,any',
            'unread_only' => 'sometimes|boolean',
            'cursor' => 'sometimes|string',
            'limit' => 'sometimes|integer|min:1|max:100',
        ]);

        return Response::structured($this->store->listThreads(
            $this->storeId($request),
            $v['channel'] ?? 'any',
            $v['unread_only'] ?? false,
            $v['cursor'] ?? null,
            $v['limit'] ?? 20,
        ));
    }
}
