<?php

namespace Meg4\Mgfp\Contracts;

use Laravel\Mcp\Request;

/**
 * Resuelve **qué tienda** está hablando, a partir del token OAuth del request.
 *
 * Es la clave del multi-tenant: MEG4 manda el token de ESA tienda, y aquí lo
 * traduces a tu identificador interno de tienda. Cámbialo si tu modelo de datos
 * scopea distinto (por usuario, por organización, etc.).
 */
interface StoreContext
{
    public function currentStoreId(Request $request): string;
}
