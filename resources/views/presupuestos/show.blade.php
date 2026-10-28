@extends('layouts.app')
@section('title','Detalle del presupuesto')
@section('breadcrumb','Presupuestos / Documento')
@section('content')
@php
 $nombreEstado=ucfirst($presupuesto->estado);
 $dias=$presupuesto->diasRestantes();
@endphp
<div class="page-head"><div><a class="back-link" href="{{ route('presupuestos.index') }}">← Volver al historial</a><div class="eyebrow ink">DETALLE DE DOCUMENTO</div><h1>{{ $presupuesto->numero?:'Borrador #'.$presupuesto->id }}</h1><p>Creado el {{ $presupuesto->fecha->format('d/m/Y') }} · Responsable: {{ $presupuesto->usuario->nombre }}</p></div>
 <div class="head-actions">
  @if($presupuesto->estado==='borrador')
   <a href="{{ route('presupuestos.edit',$presupuesto) }}" class="btn secondary">Editar borrador</a>
   <form method="post" action="{{ route('presupuestos.emitir',$presupuesto) }}" onsubmit="return confirm('¿Emitir este presupuesto? Su contenido quedará bloqueado.')">@csrf<button class="btn primary">Emitir presupuesto</button></form>
  @else
   <form method="post" action="{{ route('presupuestos.duplicar',$presupuesto) }}" onsubmit="return confirm('Se creará un borrador nuevo con los mismos productos y los precios vigentes del catálogo. ¿Continuar?')">@csrf<button class="btn secondary" type="submit">⧉ Duplicar como borrador</button></form>
   <a class="btn primary" href="{{ route('presupuestos.pdf',$presupuesto) }}">↓ Descargar PDF</a>
  @endif
 </div></div>

@if($presupuesto->estado==='vencido')
 <div class="alert warning"><b>Presupuesto vencido.</b> La oferta terminó el {{ $presupuesto->vigencia->format('d/m/Y') }}. Para continuar, duplícalo como borrador y emite uno nuevo con los precios actuales.</div>
@elseif($presupuesto->estado==='anulado')
 <div class="alert danger"><b>Documento anulado.</b> No debe entregarse como oferta válida.</div>
@elseif($presupuesto->porVencer())
 <div class="alert warning"><b>Por vencer.</b> {{ $dias===0?'La vigencia termina hoy.':'Quedan '.$dias.' día(s) de vigencia.' }}</div>
@endif

<div class="detail-grid"><div><section class="panel padded"><div class="panel-head"><h2>Resumen del documento</h2><span class="pill state-{{ $presupuesto->estado }}">{{ $nombreEstado }}</span></div><div class="detail-meta"><div><span>CLIENTE</span><b>{{ $presupuesto->cliente->nombre }}</b><small>{{ $presupuesto->cliente->documento?:'Sin documento' }}</small></div><div><span>FECHA / VIGENCIA</span><b>{{ $presupuesto->fecha->format('d/m/Y') }}</b><small>Válido hasta {{ $presupuesto->vigencia->format('d/m/Y') }}</small></div><div><span>MONEDA</span><b>Soles peruanos (PEN)</b><small>Precios finales del catálogo</small></div></div><div class="table-scroll"><table><thead><tr><th>Producto / descripción guardada</th><th>Cantidad</th><th>Precio unit.</th><th>Importe</th></tr></thead><tbody>@foreach($presupuesto->detalles as $d)<tr><td class="strong">{{ $d->descripcion_snapshot }}</td><td>{{ $d->cantidad }}</td><td>S/ {{ number_format($d->precio_snapshot,2) }}</td><td class="price">S/ {{ number_format($d->subtotal,2) }}</td></tr>@endforeach</tbody></table></div><div class="document-total"><span>TOTAL</span><strong>S/ {{ number_format($presupuesto->total,2) }}</strong></div><p class="tiny-muted">Documento comercial de presupuesto. No es una factura ni comprobante de pago.</p></section></div>
<div><section class="panel padded"><h2>Seguimiento y trazabilidad</h2><p class="muted">Las transiciones se registran sin sustituir el historial anterior.</p><div class="timeline">@foreach($presupuesto->historial as $h)<div class="timeline-item"><div class="timeline-dot"></div><div><b>{{ ucfirst($h->estado_nuevo) }}</b><small>{{ $h->created_at->format('d/m/Y H:i') }} · {{ $h->esAutomatico()?'Sistema':($h->usuario?->nombre??'—') }}</small><p>{{ $h->motivo }}</p></div></div>@endforeach</div></section>
@if(auth()->user()->esAdmin() && count($transiciones))
<section class="panel padded"><h2>Actualizar estado comercial</h2><p class="muted">Solo disponible para administración. Las opciones dependen de la vigencia de la oferta.</p>
<form class="stackform" action="{{ route('presupuestos.estado',$presupuesto) }}" method="post" id="estadoForm">@csrf
 <label>Nuevo estado<select name="estado" id="estadoSelect" required>@foreach($transiciones as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach</select></label>
 <label>Motivo <small class="field-hint" id="motivoHint">(opcional)</small><input name="motivo" id="motivoInput" maxlength="255" placeholder="Breve observación"></label>
 <button class="btn secondary full" onclick="return confirm('¿Confirmas el cambio de estado? No se podrá revertir.')">Registrar cambio</button></form></section>
@push('scripts')<script>
(()=>{const s=document.getElementById('estadoSelect'),m=document.getElementById('motivoInput'),h=document.getElementById('motivoHint');if(!s)return;
const sync=()=>{const req=['rechazado','anulado'].includes(s.value);m.required=req;m.minLength=req?5:0;h.textContent=req?'(obligatorio, mín. 5 caracteres)':'(opcional)'};s.addEventListener('change',sync);sync();})();
</script>@endpush
@endif
</div></div>
@endsection
