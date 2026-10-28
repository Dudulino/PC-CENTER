<?php
namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Registro de eventos de seguridad y operación (storage/logs/auditoria-AAAA-MM-DD.log).
 * Guarda usuario, operación, fecha e IP. Nunca debe recibir contraseñas ni datos secretos.
 */
class Bitacora
{
    public static function registrar(string $operacion, array $contexto = []): void
    {
        try {
            Log::build(['driver' => 'daily', 'path' => storage_path('logs/auditoria.log'), 'days' => 90])
                ->info($operacion, ['usuario_id' => auth()->id(), 'ip' => request()?->ip()] + $contexto);
        } catch (\Throwable) {
            // La bitácora nunca debe interrumpir la operación principal.
        }
    }
}
