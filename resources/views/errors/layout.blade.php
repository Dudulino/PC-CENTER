<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>@yield('code') · PC Center</title>
<link rel="icon" type="image/png" href="{{ asset('img/logo-pccenter-tesina.png') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v=3.1"></head>
<body><main class="error-page"><div class="error-card">
<img src="{{ asset('img/logo-pccenter-tesina.png') }}" alt="PC Center">
<div class="error-code">@yield('code')</div>
<h1>@yield('title')</h1>
<p>@yield('message')</p>
<a class="btn primary" href="{{ url('/panel') }}">Ir al panel</a><a class="btn secondary" href="javascript:history.back()">Volver</a>
</div></main></body></html>
