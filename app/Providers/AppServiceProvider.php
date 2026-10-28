<?php
namespace App\Providers;
use App\Models\Presupuesto;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}
    public function boot(): void {
        date_default_timezone_set('America/Lima');
        // Insignia con los borradores pendientes del usuario en el menú lateral.
        View::composer('layouts.app', function ($view) {
            $view->with('borradoresPendientes', auth()->check()
                ? Presupuesto::where('estado', 'borrador')->where('usuario_id', auth()->id())->count() : 0);
        });
    }
}
