<?php

/**
 * Rutas MCP — MGFP/1.0 (publicado por meg4/mgfp-laravel).
 *
 * Expone tu tienda como un **MCP server estándar y genérico en `/mcp`** — sirve
 * a CUALQUIER asistente (Claude, ChatGPT, MEG4…), no es MEG4-specific. MEG4 es
 * solo un cliente más. El path es tuyo; MGFP no lo impone (declara tu `mcp_url`
 * en el service.json).
 */

use Laravel\Mcp\Facades\Mcp;
use Meg4\Mgfp\Mcp\CommerceServer;

// Auth por defecto: TOKEN POR TIENDA (Laravel Sanctum). Fácil y seguro: el
// comercio genera un token (abilities mgfp:read / mgfp:write, revocable) y lo
// pega en MEG4. Validar = un guard. Un token por tienda → multi-tienda nativo.
Mcp::web('/mcp', CommerceServer::class)
    ->middleware(['throttle:mcp', 'auth:sanctum']);

// OPCIONAL (premium): OAuth 2.1 click-to-connect con Passport. Si lo prefieres,
// descomenta y cambia el guard a 'auth:api', y en tu service.json usa
// "auth": { "type": "oauth2", ... }.
//
// Mcp::oauthRoutes();
// Mcp::web('/mcp', CommerceServer::class)->middleware(['throttle:mcp', 'auth:api']);

// Desarrollo local sin auth (NUNCA en producción):
//   Mcp::local('okastore', CommerceServer::class);
