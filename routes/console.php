<?php
use App\Services\PresupuestoService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () { $this->comment('PC Center: sistema de presupuestos.'); });

// Marca como "vencido" todo presupuesto emitido cuya vigencia ya terminó.
// Se ejecuta a mano con `php artisan presupuestos:vencer` y también de forma automática
// al abrir el panel, el historial o un documento, así que no requiere configurar el programador de tareas.
Artisan::command('presupuestos:vencer', function (PresupuestoService $servicio) {
    $n = $servicio->vencerVigencias();
    $this->info($n === 0 ? 'No hay presupuestos por vencer.' : "Se marcaron {$n} presupuesto(s) como vencidos.");
})->purpose('Vence los presupuestos emitidos cuya vigencia ya terminó');

Schedule::command('presupuestos:vencer')->dailyAt('00:05');
