<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Cabeceras básicas de endurecimiento (OWASP): evitan incrustar el sistema en otros sitios y el "sniffing" de tipos. */
class SecurityHeaders
{
 public function handle(Request $request, Closure $next): Response
 {
  $response=$next($request);
  $response->headers->set('X-Frame-Options','SAMEORIGIN');
  $response->headers->set('X-Content-Type-Options','nosniff');
  $response->headers->set('Referrer-Policy','strict-origin-when-cross-origin');
  $response->headers->set('Permissions-Policy','camera=(self), microphone=(), geolocation=()');
  return $response;
 }
}
