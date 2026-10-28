<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class CheckRole
{
 public function handle(Request $request, Closure $next, string $rol): Response
 {
   abort_unless(auth()->check() && auth()->user()->activo && auth()->user()->rol === $rol, 403, 'No tienes permiso para esta operación.');
   return $next($request);
 }
}
