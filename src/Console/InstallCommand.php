<?php

namespace Meg4\Mgfp\Console;

use Illuminate\Console\Command;

/**
 * `php artisan mgfp:install` — colapsa todo el setup en un comando.
 *
 * Publica config y rutas, recuerda el auth por token (Sanctum) y te imprime el
 * `service.json` listo para registrarte con MEG4.
 */
class InstallCommand extends Command
{
    protected $signature = 'mgfp:install {--demo : Usa FakeStore (datos de demo) en vez de tus modelos}';
    protected $description = 'Instala MGFP: publica config/rutas e imprime tu service.json para MEG4.';

    public function handle(): int
    {
        $this->info('MGFP — instalación');

        $this->call('vendor:publish', ['--tag' => 'mgfp-config', '--force' => false]);
        $this->call('vendor:publish', ['--tag' => 'mgfp-routes', '--force' => false]);
        $this->call('vendor:publish', ['--tag' => 'ai-routes', '--force' => false]);

        $this->newLine();
        $this->line('Auth: MGFP usa <info>token por tienda (Sanctum)</info>. Si aún no tienes Sanctum:');
        $this->line('  composer require laravel/sanctum && php artisan migrate');
        $this->line('Cada tienda genera su token con abilities mgfp:read / mgfp:write y lo pega en MEG4.');

        if ($this->option('demo')) {
            $this->newLine();
            $this->warn('Modo demo: agrega a tu .env  MGFP_STORE=demo  para usar datos falsos.');
        }

        $this->newLine();
        $this->info('Tu service.json para registrarte con MEG4:');
        $this->line($this->serviceJson());

        $this->newLine();
        $this->info('Listo. Siguiente: `php artisan mgfp:doctor` para verificar conformidad.');
        return self::SUCCESS;
    }

    private function serviceJson(): string
    {
        $base = rtrim((string) config('app.url', 'https://tu-dominio'), '/');
        $sid = (string) config('mgfp.service_id', 'okastore');
        return json_encode([
            'mgfp_version' => '1.0',
            'service_id' => $sid,
            'display_name' => ucfirst($sid),
            'profile' => (string) config('mgfp.profile', 'commerce'),
            'mcp_url' => "$base/mcp",
            'auth' => [
                'type' => 'api_key',
                'token_scheme' => 'Bearer',
                'whoami_url' => "$base/mcp/whoami",
                'instructions' => 'Genera un token de tienda en Ajustes → Integraciones → MEG4.',
                'scopes' => ['mgfp:read', 'mgfp:write'],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
