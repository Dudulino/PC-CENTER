@extends('layouts.app')
@section('title','Inventario de referencia')
@section('breadcrumb','Catálogo / Productos')
@section('content')
<div class="page-head">
 <div><span class="eyebrow ink">CATÁLOGO COMERCIAL</span><h1>Productos y existencias</h1><p>Catálogo de demostración con fotos ilustrativas de Internet, precios, SKU y stock referencial.</p></div>
 @if(auth()->user()->esAdmin())<button class="btn primary" type="button" id="nuevoProducto">＋ Nuevo producto</button>@endif
</div>
<div class="inventory-metrics">
 <article class="inventory-metric m-blue"><span>▦</span><div><small>Total de productos</small><b>{{ $estadisticas['total'] }}</b></div></article>
 <article class="inventory-metric m-green"><span>✓</span><div><small>Con existencias</small><b>{{ $estadisticas['disponibles'] }}</b></div></article>
 <article class="inventory-metric m-amber"><span>!</span><div><small>Stock bajo</small><b>{{ $estadisticas['bajos'] }}</b></div></article>
 <article class="inventory-metric m-red"><span>×</span><div><small>Agotados</small><b>{{ $estadisticas['agotados'] }}</b></div></article>
 </div>
<div class="inventory-metrics inventory-metrics-secondary">
 <article class="inventory-metric m-violet"><span>S/</span><div><small>Valor de costo referencial</small><b>S/ {{ number_format($estadisticas['valor']??0,2) }}</b></div></article>
 <article class="inventory-metric m-cyan"><span>≈</span><div><small>Precio de venta promedio</small><b>S/ {{ number_format($estadisticas['precio_promedio']??0,2) }}</b></div></article>
 <article class="inventory-metric m-slate"><span>●</span><div><small>Productos activos</small><b>{{ $estadisticas['activos'] }}</b></div></article>
 </div>
<section class="panel inventory-main">
 <div class="inventory-heading"><div><h2>Inventario de referencia</h2><p>Las existencias son informativas: un presupuesto no descuenta unidades.</p></div><a class="btn secondary" href="{{ route('presupuestos.create') }}">＋ Preparar presupuesto</a></div>
 <form class="inventory-filters" method="get" action="{{ route('productos.index') }}">
  <input type="hidden" name="vista" value="{{ $vista }}">
  <label>Buscar producto o SKU<input name="q" value="{{ request('q') }}" placeholder="Buscar: SSD, memoria, PC-..." maxlength="80"></label>
  <label>Categoría<select name="categoria"><option value="">Todas las categorías</option>@foreach($categorias as $categoria)<option value="{{ $categoria }}" @selected(request('categoria')===$categoria)>{{ $categoria }}</option>@endforeach</select></label>
  <label>Estado<select name="estado"><option value="">Todos</option>@foreach(['activo'=>'Activo','inactivo'=>'Inactivo','disponible'=>'Con stock','bajo'=>'Stock bajo','agotado'=>'Agotado'] as $v=>$t)<option value="{{ $v }}" @selected(request('estado')===$v)>{{ $t }}</option>@endforeach</select></label>
  <button class="btn primary" type="submit">Filtrar</button><a class="btn secondary" href="{{ route('productos.index') }}">Limpiar</a>
 </form>
 <p class="image-license-note">Fotos ilustrativas obtenidas de Wikimedia Commons (no representan necesariamente el modelo exacto). <a href="{{ route('imagenes.creditos') }}">Autores y licencias de las fotografías</a>.</p>
 <div class="inventory-switchbar"><span class="muted">{{ $productos->total() }} resultados · precios en soles (PEN)</span><div class="view-toggle"><a class="{{ $vista==='lista'?'selected':'' }}" href="{{ request()->fullUrlWithQuery(['vista'=>'lista','page'=>1]) }}">☷ Lista</a><a class="{{ $vista==='tarjetas'?'selected':'' }}" href="{{ request()->fullUrlWithQuery(['vista'=>'tarjetas','page'=>1]) }}">▦ Tarjetas</a></div></div>
 @if($vista==='tarjetas')
 <div class="inventory-cards">
 @forelse($productos as $p)
 <article class="inventory-product-card"><div class="product-art art-{{ $p->es_servicio?'servicio':'producto' }}" aria-hidden="true"><img src="{{ $p->imagen_remota ?: asset('img/catalogo/'.$p->imagen_catalogo) }}" data-fallback="{{ asset('img/catalogo/'.$p->imagen_catalogo) }}" loading="lazy" decoding="async" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src=this.dataset.fallback" alt="Imagen ilustrativa de {{ $p->categoria }}"></div>
  <div class="card-meta"><span>{{ $p->categoria }}</span><span class="pill {{ $p->activo?'good':'off' }}">{{ $p->activo?'Activo':'Inactivo' }}</span></div>
  <h3>{{ $p->descripcion }}</h3><small class="product-code">{{ $p->codigo }}</small>
  <div class="card-price">S/ {{ number_format($p->precio,2) }}</div>
  <div class="card-bottom">
  <span class="inventory-status is-{{ $p->inventario_estado }}">
    {{ $p->disponibilidad_texto }}
  </span>
   @if(auth()->user()->esAdmin())<span class="row-actions"><button type="button" class="btn link-btn js-edit-product" data-product='{{ json_encode($p->only(["id","codigo","sku","descripcion","categoria","precio","costo","stock","stock_minimo","activo","es_servicio","icono"]), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) }}'>Editar</button><form method="post" action="{{ route('productos.activo',$p) }}" class="inline-form">@csrf @method('PATCH')<button class="btn link-btn" type="submit">{{ $p->activo?'Desactivar':'Activar' }}</button></form></span>@endif</div></article>
 @empty
<div class="empty-state">No encontramos productos para esos filtros.</div>
@endforelse

 </div>
 @else
 <div class="table-scroll"><table class="inventory-table"><thead><tr><th>Producto</th><th>Categoría</th><th>SKU / código</th><th>Stock</th><th>Costo ref.</th><th>Precio venta</th><th>Estado</th><th></th></tr></thead><tbody>
 @forelse($productos as $p)
 <tr><td><div class="inventory-product-name"><span class="table-product-emoji"><img src="{{ $p->imagen_remota ?: asset('img/catalogo/'.$p->imagen_catalogo) }}" data-fallback="{{ asset('img/catalogo/'.$p->imagen_catalogo) }}" loading="lazy" decoding="async" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src=this.dataset.fallback" alt="Imagen ilustrativa de {{ $p->categoria }}"></span><b>{{ $p->descripcion }}</b></div></td><td>{{ $p->categoria }}</td><td><code class="code-chip">{{ $p->sku?:$p->codigo }}</code><small class="table-secondary">{{ $p->codigo }}</small></td><td><span class="inventory-status is-{{ $p->inventario_estado }}">{{ $p->es_servicio?'Servicio':($p->stock===null?'Sin dato':$p->stock) }}</span>@if($p->inventario_estado==='bajo')<small class="table-secondary">Mínimo: {{ $p->stock_minimo }}</small>@endif</td><td>S/ {{ number_format($p->costo,2) }}</td><td class="price">S/ {{ number_format($p->precio,2) }}</td><td><span class="pill {{ $p->activo?'good':'off' }}">{{ $p->activo?'Activo':'Inactivo' }}</span></td><td>@if(auth()->user()->esAdmin())<span class="row-actions"><button type="button" class="btn link-btn js-edit-product" data-product='{{ json_encode($p->only(["id","codigo","sku","descripcion","categoria","precio","costo","stock","stock_minimo","activo","es_servicio","icono"]), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) }}'>Editar</button><form method="post" action="{{ route('productos.activo',$p) }}" class="inline-form">@csrf @method('PATCH')<button class="btn link-btn" type="submit">{{ $p->activo?'Desactivar':'Activar' }}</button></form></span>@else<span class="muted">Consulta</span>@endif</td></tr>
 @empty
<tr><td colspan="8" class="empty-cell">No se encontraron productos.</td></tr>
@endforelse

 </tbody></table></div>
 @endif
 <div class="pagination">{{ $productos->links('vendor.pagination.simple') }}</div>
</section>
@if(auth()->user()->esAdmin())
<dialog class="modal inventory-dialog" id="productoDialog"><form method="post" id="productoForm" action="{{ route('productos.store') }}">@csrf<input type="hidden" name="_method" id="productoMethod" value="POST">
 <div class="modal-head"><h2 id="productoTitle">Agregar producto</h2><button class="xclose" type="button" id="cerrarProducto">×</button></div>
 <div class="modal-body">
 <div class="form-grid"><label>Código de producto<input name="codigo" id="pCodigo" maxlength="64" required placeholder="Ej. 000123450039"></label><label>SKU opcional<input name="sku" id="pSku" maxlength="64" placeholder="PC-000123450039"></label></div>
 <label>Nombre / descripción<input name="descripcion" id="pDescripcion" maxlength="255" required></label>
 <div class="form-grid"><label>Categoría<input name="categoria" id="pCategoria" required maxlength="60" list="categoriasSugeridas" value="Periféricos"><datalist id="categoriasSugeridas">@foreach($categorias as $categoria)<option value="{{ $categoria }}">@endforeach</datalist></label><label>Icono visual<input name="icono" id="pIcono" maxlength="20" required value="📦" placeholder="📦"></label></div>
 <div class="form-grid"><label>Costo referencial (S/)<input name="costo" id="pCosto" type="number" step="0.01" min="0" required value="0"></label><label>Precio de venta (S/)<input name="precio" id="pPrecio" type="number" step="0.01" min="0.01" required></label></div>
 <div class="form-grid"><label>Stock referencial<input name="stock" id="pStock" type="number" min="0" value="0"><small class="field-hint" id="pStockHint">No aplica a servicios.</small></label><label>Alerta de stock mínimo<input name="stock_minimo" id="pMinimo" type="number" min="0" required value="5"></label></div>
 <div class="form-grid"><label>Tipo<select name="es_servicio" id="pServicio"><option value="0">Producto físico</option><option value="1">Servicio (sin stock)</option></select></label><label>Estado<select name="activo" id="pActivo"><option value="1">Activo</option><option value="0">Inactivo</option></select></label></div>
 <p class="tiny-muted">Este es un catálogo de referencia para presupuestar, no un control automático de ventas ni un registro tributario.</p>
 </div><div class="modal-actions"><button type="button" class="btn secondary" id="cancelarProducto">Cancelar</button><button class="btn primary" type="submit">Guardar producto</button></div></form></dialog>
@push('scripts')<script src="{{ asset('js/products.js') }}?v=2"></script>@endpush
@endif
@endsection
