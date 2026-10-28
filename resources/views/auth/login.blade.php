<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Acceso · PC Center</title><link rel="stylesheet" href="{{ asset('css/app.css') }}?v=3.1"><link rel="icon" type="image/png" href="{{ asset('img/logo-pccenter-tesina.png') }}"></head>
<body class="login-body"><main class="login-frame">
 <div class="login-story"><div class="login-brand"><span class="brand-symbol">PC</span> PC CENTER</div><div class="login-art"><img src="{{ asset('img/logo-pccenter-tesina.png') }}" alt="Identidad PC Center"></div><span class="eyebrow">SISTEMA DE PRESUPUESTOS</span><h1>Presupuestos claros.<br><em>Atención más ágil.</em></h1><p>Identifica artículos por código, consulta precios vigentes y emite documentos PDF con trazabilidad.</p><div class="login-points"><span>✓ Catálogo centralizado</span><span>✓ Escaneo de productos</span><span>✓ Historial comercial</span></div><small class="login-disclaimer">Prototipo académico con información ficticia</small></div>
 <div class="login-formside"><div class="login-panel"><div class="welcome-icon">↗</div><span class="eyebrow ink">ACCESO SEGURO</span><h2>Bienvenido de nuevo</h2><p>Ingresa tus credenciales para continuar.</p>
  @if(session('ok'))<div class="alert success">{{ session('ok') }}</div>@endif
  @if($errors->any())<div class="alert danger">{{ $errors->first() }}</div>@endif
  <form method="post" action="{{ route('login.post') }}" class="stackform">@csrf
   <label>Correo electrónico<input name="correo" type="email" placeholder="nombre@empresa.com" value="{{ old('correo') }}" required autofocus autocomplete="username"></label>
   <label>Contraseña<span class="password-wrap"><input name="password" id="loginPassword" type="password" placeholder="••••••••" required autocomplete="current-password"><button type="button" class="password-toggle" id="togglePassword" aria-label="Mostrar u ocultar la contraseña" aria-pressed="false">Mostrar</button></span></label>
   <button class="btn primary full" type="submit">Ingresar al sistema <span>→</span></button>
  </form>
  @if(config('app.demo'))
  <div class="demo-credentials"><strong>Acceso para exposición</strong><p>Administrador: <code>admin@pccenter.test</code></p><p>Atención: <code>atencion@pccenter.test</code></p><p>Clave de ambos: <code>Demo2026!</code></p></div>
  <div class="login-foot">Los usuarios y productos son exclusivamente de demostración.</div>
  @endif
 </div></div>
</main>
<script>
(()=>{const i=document.getElementById('loginPassword'),b=document.getElementById('togglePassword');if(!i||!b)return;
b.addEventListener('click',()=>{const show=i.type==='password';i.type=show?'text':'password';b.textContent=show?'Ocultar':'Mostrar';b.setAttribute('aria-pressed',String(show));});})();
</script></body></html>
