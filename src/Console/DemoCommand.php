<?php

namespace Meg4\Mgfp\Console;

use Illuminate\Console\Command;
use Meg4\Mgfp\Support\FakeStore;

/**
 * `php artisan mgfp:demo` — prueba instantánea con datos falsos (FakeStore).
 *
 * Lista las tools que expondrás (según config) y ejecuta un par de llamadas
 * de ejemplo, sin tocar tu base de datos. Para ver que "funciona" en 30 s.
 */
class DemoCommand extends Command
{
    protected $signature = 'mgfp:demo';
    protected $description = 'Corre una demo MGFP con datos falsos (sin tu base de datos).';

    public function handle(): int
    {
        $store = new FakeStore();
        $caps = (array) config('mgfp.capabilities', []);

        $this->info('Tools que MEG4 verá (según config mgfp.capabilities):');
        foreach ($caps as $c) {
            $write = in_array($c, ['create_invoice', 'reply_message'], true);
            $this->line('  · ' . $c . ($write ? '  <comment>[escritura]</comment>' : ''));
        }

        $this->newLine();
        $this->info('Ejemplo list_products:');
        $this->line(json_encode($store->listProducts('demo-store', null, null, 5), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->newLine();
        $this->info('Ejemplo unread_summary:');
        $this->line(json_encode($store->unreadSummary('demo-store', 3), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->newLine();
        $this->line('Si esto se ve bien, tu MCP server responde igual con tus datos reales.');
        return self::SUCCESS;
    }
}
