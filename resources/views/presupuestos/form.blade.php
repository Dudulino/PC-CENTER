@extends('layouts.app')
@section('title',$presupuesto?'Editar presupuesto':'Nuevo presupuesto')
@section('breadcrumb','Presupuestos / '.($presupuesto?'Editar borrador':'Nuevo'))
@section('content')
<div class="page-head"><div><span class="eyebrow ink">PUNTO DE COTIZACIÓN · PC CENTER</span><h1>{{ $presupuesto?'Editar borrador #'.$presupuesto->id:'Nuevo presupuesto' }}</h1><p>Elige productos del catálogo o escanea sus códigos. Precios en soles.</p></div><a class="btn secondary" href="{{ $presupuesto?route('presupuestos.show',$presupuesto):route('presupuestos.index') }}">← Volver</a></div>
<div class="pos-client-bar panel padded"><div class="pos-client-info"><div class="pos-step">01</div><div><h2>¿A quién cotizamos?</h2><p class="muted">Selecciona un cliente existente o crea uno desde aquí.</p></div></div>
<div class="client-inline"><label class="grow">Cliente / razón social<select id="clienteId" form="presupuestoForm" name="cliente_id" required><option value="">Selecciona un cliente</option>@foreach($clientes as $c)<option value="{{ $c->id }}" @selected(old('cliente_id',$presupuesto?->cliente_id)==$c->id)>{{ $c->nombre }}{{ $c->documento?' · '.$c->documento:'' }}</option>@endforeach</select></label><button type="button" class="btn secondary align-bottom" id="newClientButton">＋ Cliente</button></div></div>
<div class="pos-workspace">
 <div class="pos-catalog panel padded">
  <div class="pos-section-head"><div class="pos-step">02</div><div><h2>Selecciona productos</h2><p class="muted">{{ $catalogo->count() }} productos y servicios listos para agregar.</p></div><div class="pos-scanner-pill">▥ Escáner USB / cámara</div></div>
  <div class="pos-searchbar"><span aria-hidden="true">⌕</span><input id="productSearch" type="search" placeholder="Buscar por nombre, código o SKU..." autocomplete="off" aria-label="Buscar productos"><button class="btn secondary" type="button" id="cameraButton">▣ Escanear</button></div>
  <div class="shortcut-hint">Atajos: <kbd>F2</kbd> ir al campo de código · <kbd>Esc</kbd> limpiar búsqueda</div>
  <div class="scan-actions"><label class="scan-shortcut">Código de barras <input id="scanCode" placeholder="Ej.: 000123450001" autocomplete="off" aria-label="Código para lector USB"></label><button class="btn secondary" type="button" id="scanButton">Agregar por código</button></div>
  <div id="scanMsg" class="scan-helper" role="status">También puedes seleccionar una tarjeta. Los códigos conservan sus ceros iniciales.</div>
  <div id="cameraPanel" hidden><video id="cameraVideo" autoplay muted playsinline></video><button type="button" class="btn secondary" id="stopCamera">Cerrar cámara</button></div>
  <div id="searchResults" class="suggestion-list" aria-live="polite"></div>
  <div class="pos-category-scroll" id="posCategories" aria-label="Categorías de productos">
   <button type="button" class="pos-category active" data-category="">Todos <span>{{ $catalogo->count() }}</span></button>
   @foreach($categorias as $categoria)<button type="button" class="pos-category" data-category="{{ $categoria }}">{{ $categoria }}</button>@endforeach
  </div>
  <div class="pos-product-grid" id="posCatalogGrid">
   @foreach($catalogo as $p)
   <button type="button" class="pos-product-card" data-id="{{ $p->id }}" data-code="{{ $p->codigo }}" data-description="{{ $p->descripcion }}" data-price="{{ $p->precio }}" data-stock="{{ $p->es_servicio||$p->stock===null?'':$p->stock }}" data-category="{{ $p->categoria }}" data-search="{{ mb_strtolower($p->descripcion.' '.$p->codigo.' '.($p->sku??'')) }}" aria-label="Agregar {{ $p->descripcion }} al presupuesto">
    <span class="pos-product-visual" aria-hidden="true"><img src="{{ $p->imagen_remota ?: asset('img/catalogo/'.$p->imagen_catalogo) }}" data-fallback="{{ asset('img/catalogo/'.$p->imagen_catalogo) }}" loading="lazy" decoding="async" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src=this.dataset.fallback" alt="Imagen ilustrativa de {{ $p->categoria }}"></span>
    <span class="pos-product-name">{{ $p->descripcion }}</span><span class="pos-product-price">S/ {{ number_format($p->precio,2) }}</span>
    <span class="pos-product-availability is-{{ $p->inventario_estado }}">{{ $p->disponibilidad_texto }}</span>
    <span class="pos-add-icon" aria-hidden="true">＋</span>
   </button>
   @endforeach
  </div>
  <p class="image-license-note">Fotografías referenciales de Wikimedia Commons. <a href="{{ route('imagenes.creditos') }}">Ver créditos de imágenes</a>. Si no hay Internet se muestra una ilustración local.</p>
  <p id="posEmpty" class="empty-state" hidden>No hay coincidencias. Intenta otra búsqueda o categoría.</p>
 </div>
 <div class="pos-cart-col">
  <section class="panel padded pos-cart"><div class="pos-section-head"><div class="pos-step">03</div><div><h2>Detalle del presupuesto</h2><p class="muted">Revisa cantidades y precios antes de guardar.</p></div></div>
   <div class="pos-cart-table-wrap"><table class="lines-table pos-lines"><thead><tr><th>Artículo</th><th>Cant.</th><th>Subtotal</th><th></th></tr></thead><tbody id="itemsBody"><tr><td colspan="4" class="empty-cell">Selecciona un producto para comenzar.</td></tr></tbody></table></div>
   <div class="pos-cart-actions"><button type="button" class="btn link-btn" id="clearCart">Vaciar detalle</button></div><div id="stockWarning" class="pos-warning" role="status" hidden></div><div class="pos-cart-summary"><div><span>Productos diferentes</span><b id="itemCount">0</b></div><div><span>Unidades</span><b id="unitCount">0</b></div><div class="pos-grand-total"><span>Total cotizado</span><strong id="quoteTotal">S/ 0.00</strong></div></div>
   <div class="pos-notice">El importe es estimado: el servidor lo recalcula con los precios vigentes al guardar. El stock es solo referencial: confirma la disponibilidad antes de vender.</div>
  </section>
  <section class="panel padded pos-finalize"><h2>Finalizar presupuesto</h2><p class="muted">Esta operación no cobra ni genera una boleta SUNAT.</p>
   <form id="presupuestoForm" method="post" action="{{ $presupuesto?route('presupuestos.update',$presupuesto):route('presupuestos.store') }}">@csrf @if($presupuesto)@method('PUT')@endif
   <label>Fecha de elaboración<input type="text" readonly value="{{ $presupuesto?$presupuesto->fecha->format('d/m/Y'):now()->format('d/m/Y') }}"></label>
   <label>Vigencia de la oferta<input type="date" name="vigencia" required min="{{ now()->toDateString() }}" value="{{ old('vigencia',$presupuesto?$presupuesto->vigencia->format('Y-m-d'):now()->addDays(7)->format('Y-m-d')) }}"></label>
   <input type="hidden" id="itemsJson" name="items" value="[]"><button class="btn primary full pos-save" type="submit" id="saveQuote">Guardar presupuesto →</button></form>
   <div class="security-note">⌁ &nbsp;Al emitir, el sistema conserva los precios y descripciones históricos.</div>
  </section>
 </div>
</div>
<dialog class="modal" id="quickClientDialog"><form id="quickClientForm" action="{{ route('clientes.store') }}"><div class="modal-head"><h2>Nuevo cliente</h2><button class="xclose" type="button" onclick="document.getElementById('quickClientDialog').close()">×</button></div><div class="modal-body"><label>Nombre / razón social<input name="nombre" required maxlength="180"></label><div class="form-grid"><label>Documento<input name="documento" maxlength="20"></label><label>Teléfono<input name="telefono" maxlength="30"></label></div><label>Correo<input name="correo" type="email"></label><p id="quickClientMsg" class="error-text"></p></div><div class="modal-actions"><button type="button" class="btn secondary" onclick="document.getElementById('quickClientDialog').close()">Cancelar</button><button class="btn primary" type="submit">Crear y seleccionar</button></div></form></dialog>
@php
 $itemsInicial=$presupuesto? $presupuesto->detalles->map(fn($d)=>['producto_id'=>$d->producto_id,'codigo'=>$d->producto?->codigo??'','descripcion'=>$d->descripcion_snapshot,'precio'=>$d->precio_snapshot,'cantidad'=>$d->cantidad,'stock'=>($d->producto&&!$d->producto->es_servicio)?$d->producto->stock:null])->all() : [];
@endphp
@push('scripts')<script>
window.quoteSetup={
 urlSearch: @json(route('productos.buscar')),
 urlCode: @json(route('productos.codigo')),
 token: @json(csrf_token()),
 items: {!! json_encode($itemsInicial,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) !!}
};
</script><script src="{{ asset('js/quote.js') }}?v=3.0"></script>@endpush
@endsection
