@extends('layouts.app')
@section('title','Resumen general')
@section('breadcrumb','Resumen general')
@section('content')
<div class="page-head"><div><span class="eyebrow ink">VISIÓN GENERAL</span><h1>Resumen comercial</h1><p>Actividad de presupuestos, catálogo y clientes de PC Center.</p></div><a class="btn primary" href="{{ route('presupuestos.create') }}">＋ Nuevo presupuesto</a></div>
@if($misBorradores>0)<div class="alert info"><span>✎</span>Tienes <b>{{ $misBorradores }}</b> borrador(es) pendiente(s) de emitir. <a class="text-link" href="{{ route('presupuestos.index',['estado'=>'borrador']) }}">Revisarlos →</a></div>@endif
<div class="grid-stats">
 <div class="stat-card"><div class="stat-title">Presupuestos registrados <span class="stat-icon cyan">▤</span></div><div class="stat-num">{{ $totales['presupuestos'] }}</div><div class="stat-note">Borradores y documentos emitidos</div></div>
 <div class="stat-card"><div class="stat-title">Productos activos <span class="stat-icon violet">▦</span></div><div class="stat-num">{{ $totales['productos'] }}</div><div class="stat-note">Disponibles en catálogo</div></div>
 <div class="stat-card"><div class="stat-title">Clientes registrados <span class="stat-icon amber">♙</span></div><div class="stat-num">{{ $totales['clientes'] }}</div><div class="stat-note">Base de clientes de prueba</div></div>
 <div class="stat-card"><div class="stat-title">Importe emitido / aceptado <span class="stat-icon green">S/</span></div><div class="stat-num smallnum">S/ {{ number_format($monto,2) }}</div><div class="stat-note">No corresponde a ingresos cobrados</div></div>
</div>
<div class="grid-main"><section class="panel"><div class="panel-head"><div><h2>Presupuestos recientes</h2><p>Últimos documentos preparados</p></div><a class="text-link" href="{{ route('presupuestos.index') }}">Ver todos →</a></div>
 <div class="table-scroll"><table><thead><tr><th>Documento</th><th>Cliente</th><th>Total</th><th>Estado</th><th></th></tr></thead><tbody>
 @forelse($recientes as $p)
<tr><td class="strong">{{ $p->numero ?: 'Borrador #'.$p->id }}</td><td>{{ $p->cliente->nombre }}</td><td>S/ {{ number_format($p->total,2) }}</td><td><span class="pill state-{{ $p->estado }}">{{ ucfirst($p->estado) }}</span></td><td><a class="text-link" href="{{ route('presupuestos.show',$p) }}">Abrir</a></td></tr>
@empty
<tr><td colspan="5" class="empty-cell">Aún no hay presupuestos. Crea uno para comenzar.</td></tr>
@endforelse
 </tbody></table></div></section>
 <section class="panel"><div class="panel-head"><div><h2>Estado de documentos</h2><p>Distribución actual</p></div></div><div class="state-overview">
 @foreach(['borrador','emitido','aceptado','rechazado','vencido','anulado'] as $estado)
 <div class="state-row"><span>{{ ucfirst($estado) }}</span><div class="state-track"><div class="state-fill fill-{{ $estado }}" style="width:{{ $totales['presupuestos'] ? (($estados[$estado]??0)*100/$totales['presupuestos']) : 0 }}%"></div></div><b>{{ $estados[$estado]??0 }}</b></div>
 @endforeach</div><div class="help-block"><b>¿Cómo funciona?</b><p>El lector USB escribe el código como teclado. El servidor valida el producto, su precio y las cantidades antes de guardar.</p></div></section></div>

<div class="grid-main grid-main-3">
 <section class="panel"><div class="panel-head"><div><h2>Importe por mes</h2><p>Emitido o aceptado, últimos 6 meses (S/)</p></div></div>
  <div class="bar-chart" role="img" aria-label="Importe emitido o aceptado por mes">
   @foreach($meses as $m)
   <div class="bar-col"><span class="bar-value">{{ $m['importe']>0?number_format($m['importe'],0):'' }}</span><div class="bar-track"><div class="bar-fill" style="height:{{ round($m['importe']*100/$maxMes) }}%"></div></div><span class="bar-label">{{ $m['etiqueta'] }}</span><small class="bar-count">{{ $m['cantidad'] }} doc.</small></div>
   @endforeach
  </div></section>
 <section class="panel"><div class="panel-head"><div><h2>Por vencer</h2><p>Emitidos con vigencia ≤ {{ \App\Models\Presupuesto::DIAS_AVISO_VENCIMIENTO }} días</p></div></div>
  <ul class="mini-list">
   @forelse($porVencer as $p)
   <li><a href="{{ route('presupuestos.show',$p) }}"><b>{{ $p->numero }}</b><span>{{ $p->cliente->nombre }}</span></a><small>{{ $p->vigencia->format('d/m/Y') }}</small></li>
   @empty
   <li class="mini-empty">Ningún presupuesto próximo a vencer.</li>
   @endforelse
  </ul></section>
 <section class="panel"><div class="panel-head"><div><h2>Stock bajo</h2><p>Referencial, según el catálogo</p></div><a class="text-link" href="{{ route('productos.index',['estado'=>'bajo']) }}">Ver →</a></div>
  <ul class="mini-list">
   @forelse($stockBajo as $p)
   <li><a href="{{ route('productos.index',['q'=>$p->descripcion]) }}"><b>{{ $p->descripcion }}</b></a><small>{{ $p->stock }} / mín. {{ $p->stock_minimo }}</small></li>
   @empty
   <li class="mini-empty">Sin alertas de stock.</li>
   @endforelse
  </ul></section>
</div>
<div class="workflow"><div><b>01</b><strong>Identifica</strong><span>Escanea o busca productos</span></div><span>→</span><div><b>02</b><strong>Prepara</strong><span>Selecciona cliente y cantidades</span></div><span>→</span><div><b>03</b><strong>Emite</strong><span>Guarda, emite y descarga PDF</span></div></div>
@endsection
