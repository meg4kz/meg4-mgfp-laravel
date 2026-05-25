<?php

namespace Meg4\Mgfp\Support;

use Laravel\Mcp\Request;
use Meg4\Mgfp\Contracts\StoreContext;

/**
 * Resolución por defecto de la tienda actual desde el token OAuth.
 *
 * Estrategia (ajústala a tu modelo): toma el `store_id` del usuario/cliente
 * autenticado por Passport. En Okastore, un token se emite **por tienda**, así
 * que el `store_id` viaja en el token y aquí lo devolvemos. Sobrescribe el
 * binding en tu `AppServiceProvider` si scopeas distinto.
 */
class TokenStoreContext implements StoreContext
{
    public function currentStoreId(Request $request): string
    {
        $user = method_exists($request, 'user') ? $request->user() : null;

        // 1) atributo directo del modelo (p.ej. columna store_id).
        if ($user && isset($user->store_id)) {
            return (string) $user->store_id;
        }
        // 2) método del modelo.
        if ($user && method_exists($user, 'currentStoreId')) {
            return (string) $user->currentStoreId();
        }
        // 3) fallback de desarrollo (FakeStore).
        return (string) (config('mgfp.default_store_id') ?? 'demo-store');
    }
}
