<?php

namespace Meg4\Mgfp\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Meg4\Mgfp\Mcp\MgfpTool;

#[IsReadOnly(true)]
#[Description('Mensajes de una conversación, más recientes primero. Para leerle el chat al dueño.')]
class ListMessagesTool extends MgfpTool
{
    protected string $name = 'list_messages';

    public function schema(JsonSchema $schema): array
    {
        return [
            'thread_id' => $schema->string()->description('Id del hilo.')->required(),
            'limit' => $schema->integer()->description('Máximo de mensajes (1-100).')->default(30),
        ];
    }

    public function handle(Request $request): Response
    {
        $v = $request->validate([
            'thread_id' => 'required|string',
            'limit' => 'sometimes|integer|min:1|max:100',
        ]);

        return Response::structured($this->store->listMessages(
            $this->storeId($request),
            $v['thread_id'],
            $v['limit'] ?? 30,
        ));
    }
}
