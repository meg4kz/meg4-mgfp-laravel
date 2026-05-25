<?php

namespace Meg4\Mgfp\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Meg4\Mgfp\Mcp\MgfpTool;

#[IsReadOnly(true)]
#[Description('Resumen de ventas/ingresos/top productos para un período. Para "¿cuánto vendí hoy?".')]
class AnalyticsSummaryTool extends MgfpTool
{
    protected string $name = 'analytics_summary';

    public function schema(JsonSchema $schema): array
    {
        return [
            'period' => $schema->string()
                ->enum(['today', 'yesterday', 'this_week', 'this_month', 'last_30d'])
                ->description('Período a resumir.')->default('today'),
        ];
    }

    public function handle(Request $request): Response
    {
        $v = $request->validate(['period' => 'sometimes|string|in:today,yesterday,this_week,this_month,last_30d']);

        return Response::structured($this->store->analyticsSummary(
            $this->storeId($request),
            $v['period'] ?? 'today',
        ));
    }
}
