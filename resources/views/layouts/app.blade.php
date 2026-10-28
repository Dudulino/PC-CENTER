<!doctype html>
<html lang="es">
<head>
 <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
 <meta name="csrf-token" content="{{ csrf_token() }}">
 <title>@yield('title','Panel') · PC Center</title>
 <link rel="icon" type="image/png" href="{{ asset('img/logo-pccenter-tesina.png') }}">
 <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=3.1">
 <script defer src="{{ asset('js/app.js') }}?v=3.1"></script>
 @stack('head')
</head>
<body>
<div class="shell">
 <aside class="sidebar" id="sidebar">
  <a class="brand" href="{{ route('panel') }}"><span class="brand-symbol">PC</span><span><b>PC CENTER</b><small>Gestión comercial</small></span></a>
  <div class="sidebar-label">ESPACIO DE TRABAJO</div>
  <nav class="nav">
   <a class="{{ request()->routeIs('panel')?'active':'' }}" href="{{ route('panel') }}"><span class="nav-icon">◫</span> Resumen general</a>
   <a class="{{ request()->routeIs('presupuestos.create')?'active':'' }}" href="{{ route('presupuestos.create') }}"><span class="nav-icon">＋</span> Nuevo presupuesto</a>
   <a class="{{ request()->routeIs('presupuestos.index','presupuestos.show','presupuestos.edit')?'active':'' }}" href="{{ route('presupuestos.index') }}"><span class="nav-icon">▤</span> Presupuestos @if(($borradoresPendientes??0)>0)<span class="nav-badge" title="Borradores pendientes">{{ $borradoresPendientes }}</span>@endif</a>
   <div class="sidebar-label sub">MANTENIMIENTO</div>
   <a class="{{ request()->routeIs('productos.*')?'active':'' }}" href="{{ route('productos.index') }}"><span class="nav-icon">▦</span> Productos</a>
   <a class="{{ request()->routeIs('clientes.*')?'active':'' }}" href="{{ route('clientes.index') }}"><span class="nav-icon">♙</span> Clientes</a>
   @if(auth()->user()->esAdmin())
   <div class="sidebar-label sub">ADMINISTRACIÓN</div>
   <a class="{{ request()->routeIs('usuarios.*')?'active':'' }}" href="{{ route('usuarios.index') }}"><span class="nav-icon">⚿</span> Usuarios y roles</a>
   <a class="{{ request()->routeIs('respaldo.*')?'active':'' }}" href="{{ route('respaldo.index') }}"><span class="nav-icon">⇩</span> Respaldos</a>
   @endif
  </nav>
  <div class="sidebar-bottom"><span class="soft-dot"></span><div><strong>Entorno de demostración</strong><small>Datos de ejemplo, no comerciales</small></div></div>
 </aside>
 <div class="mainwrap">
  <header class="topbar"><button class="mobile-menu" aria-label="Mostrar menú" onclick="document.body.classList.toggle('open-side')">☰</button>
   <div class="topbar-path">PC Center <span> / </span> @yield('breadcrumb','Panel')</div>
   <div class="userbar"><span class="user-name">{{ auth()->user()->nombre }}</span><span class="user-role">{{ auth()->user()->esAdmin()?'Administrador':'Atención comercial' }}</span><span class="avatar">{{ mb_substr(auth()->user()->nombre,0,1) }}</span><form action="{{ route('logout') }}" method="post">@csrf <button type="submit" class="logout" title="Cerrar sesión">Salir</button></form></div>
  </header>
  <main class="content">
   @if(session('ok'))<div class="alert success" role="status"><span>✓</span>{{ session('ok') }}<button type="button" class="alert-close" onclick="this.parentElement.remove()">×</button></div>@endif
   @if($errors->any())<div class="alert danger" role="alert"><b>Revisa lo siguiente:</b><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul><button type="button" class="alert-close" onclick="this.parentElement.remove()">×</button></div>@endif
   @yield('content')
  </main>
  <footer class="footer">PC Center · Prototipo académico 2026 <span><a href="{{ route('imagenes.creditos') }}">Créditos de imágenes</a> · Laravel · PHP · MySQL</span></footer>
 </div>
</div>
@stack('scripts')
</body></html>
