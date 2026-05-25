<?php

namespace Meg4\Mgfp\Console;

use Illuminate\Console\Command;
use Meg4\Mgfp\Contracts\StoreContext;
use Meg4\Mgfp\Contracts\StoreRepository;

/**
 * `php artisan mgfp:doctor` — checklist de conformidad MGFP, automatizado.
 *
 * Verifica config, binding del store, capacidades habilitadas y modelos
 * mapeados; imprime ✓/✗ por ítem para que sepas exactamente qué falta.
 */
class DoctorCommand extends Command
{
    protected $signature = 'mgfp:doctor';
    protected $description = 'Verifica que tu integración MGFP está completa y bien configurada.';

    public function handle(): int
    {
        $ok = true;

        $ok = $this->check('Config publicada (config/mgfp.php)', config('mgfp') !== null) && $ok;
        $ok = $this->check('service_id definido', (bool) config('mgfp.service_id')) && $ok;

        $bound = false;
        try {
            $bound = app(StoreRepository::class) instanceof StoreRepository;
        } catch (\Throwable $e) {
            $bound = false;
        }
        $ok = $this->check('StoreRepository resoluble', $bound) && $ok;

        $ctx = false;
        try {
            $ctx = app(StoreContext::class) instanceof StoreContext;
        } catch (\Throwable $e) {
            $ctx = false;
        }
        $ok = $this->check('StoreContext resoluble (multi-tienda)', $ctx) && $ok;

        $caps = (array) config('mgfp.capabilities', []);
        $ok = $this->check('Capacidades habilitadas (≥ núcleo)', count($caps) >= 7) && $ok;

        // modelos del auto-store (si se usa)
        $store = (string) config('mgfp.store', '');
        if (str_contains($store, 'EloquentAutoStore')) {
            foreach (['products'] as $dom) {
                $m = config("mgfp.models.$dom.model");
                $this->check("Modelo mapeado: $dom ($m)", $m && class_exists($m));
            }
        }

        $this->newLine();
        $this->line($ok ? '<info>✓ Conformidad MGFP OK</info>' : '<comment>Faltan ítems arriba (✗).</comment>');
        $this->line('Tip: `php artisan mgfp:demo` para ver las tools que expondrás.');
        return $ok ? self::SUCCESS : self::FAILURE;
    }

    private function check(string $label, bool $pass): bool
    {
        $this->line(($pass ? '<info>✓</info>' : '<error>✗</error>') . " $label");
        return $pass;
    }
}
