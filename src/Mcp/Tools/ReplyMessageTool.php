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
 * ESCRIBE — responde por el mismo canal del hilo (igual que en WhatsApp).
 * Sin `IsReadOnly` ⇒ MEG4 exige scope `:write` + confirmación de voz.
 * `IsIdempotent` + `idempotency_key` ⇒ reintentar no manda el mensaje dos veces.
 */
#[IsReadOnly(false)]
#[IsIdempotent(true)]
#[Description('Responde a una conversación en nombre del dueño, por el MISMO canal del hilo (WhatsApp/Instagram/web).')]
class ReplyMessageTool extends MgfpTool
{
    protected string $name = 'reply_message';

    public function schema(JsonSchema $schema): array
    {
        return [
            'thread_id' => $schema->string()->description('Id del hilo a responder.')->required(),
            'text' => $schema->string()->description('Texto de la respuesta.')->required(),
            'idempotency_key' => $schema->string()->description('Clave de idempotencia (obligatoria).')->required(),
        ];
    }

    public function handle(Request $request): Response
    {
        $v = $request->validate([
            'thread_id' => 'required|string',
            'text' => 'required|string|min:1',
            'idempotency_key' => 'required|string',
        ]);

        return Response::structured($this->store->replyMessage(
            $this->storeId($request),
            $v['thread_id'],
            $v['text'],
            $v['idempotency_key'],
        ));
    }
}
