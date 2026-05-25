<?php

namespace Meg4\Mgfp\Mcp;

use Laravel\Mcp\Request;
use Laravel\Mcp\Server\Tool;
use Meg4\Mgfp\Contracts\StoreContext;
use Meg4\Mgfp\Contracts\StoreRepository;

/**
 * Base de todas las tools MGFP del perfil `commerce`.
 *
 * Inyecta el {@see StoreRepository} (tus datos) y el {@see StoreContext}
 * (qué tienda habla, desde el token OAuth). Tus tools solo implementan
 * `handle()` delegando en `$this->store` con `$this->storeId($request)`.
 *
 * El nombre MCP de cada tool DEBE ser exactamente el nombre canónico MGFP
 * (`list_products`, `reply_message`, …). Lo fijamos vía `$name`.
 */
abstract class MgfpTool extends Tool
{
    public function __construct(
        protected StoreRepository $store,
        protected StoreContext $context,
    ) {}

    /** Id de la tienda actual, resuelto del token OAuth del request. */
    protected function storeId(Request $request): string
    {
        return $this->context->currentStoreId($request);
    }

    /**
     * Registra la tool SOLO si su capacidad está habilitada en
     * `config('mgfp.capabilities')`. Así el partner expone progresivamente:
     * arranca con el núcleo y agrega opcionales sin tocar código. MEG4
     * autodetecta lo expuesto por `tools/list`.
     */
    public function shouldRegister(?Request $request = null): bool
    {
        $enabled = config('mgfp.capabilities', []);
        return empty($enabled) || in_array($this->name, $enabled, true);
    }
}
