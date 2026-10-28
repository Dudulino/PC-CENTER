@extends('layouts.app')
@section('title','Historial de presupuestos')
@section('breadcrumb','Presupuestos / Historial')
@section('content')
<div class="page-head"><div><span class="eyebrow ink">GESTIÓN COMERCIAL</span><h1>Historial de presupuestos</h1><p>Consulta documentos por número, cliente, fecha y estado.</p></div><a class="btn primary" href="{{ route('presupuestos.create') }}">＋ Nuevo presupuesto</a></div>
<section class="panel"><form method="get" class="filterbar"><label>Buscar<input name="q" value="{{ request('q') }}" placeholder="N.º o nombre del cliente"></label><label>Estado<select name="estado"><option value="">Todos</option>@foreach(['borrador','emitido','aceptado','rechazado','vencido','anulado'] as $e)<option value="{{ $e }}" @selected(request('estado')===$e)>{{ ucfirst($e) }}</option>@endforeach</select></label><label>Fecha<input type="date" name="fecha" value="{{ request('fecha') }}"></label><button class="btn primary">Aplicar filtros</button><a class="btn secondary" href="{{ route('presupuestos.index') }}">Limpiar</a></form>
<div class="table-scroll"><table><thead><tr><th>Documento</th><th>Cliente</th><th>Fecha</th><th>Vigencia</th><th>Total</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
@forelse($presupuestos as $p)
<tr><td class="strong">{{ $p->numero?:'Borrador #'.$p->id }}</td><td>{{ $p->cliente->nombre }}</td><td>{{ $p->fecha->format('d/m/Y') }}</td><td>{{ $p->vigencia->format('d/m/Y') }}</td><td class="price">S/ {{ number_format($p->total,2) }}</td><td><span class="pill state-{{ $p->estado }}">{{ ucfirst($p->estado) }}</span></td><td class="action-links"><a class="text-link" href="{{ route('presupuestos.show',$p) }}">Ver</a>@if($p->numero)<a class="text-link" href="{{ route('presupuestos.pdf',$p) }}">PDF</a>@endif</td></tr>
@empty
<tr><td colspan="7" class="empty-cell">No existen presupuestos con estos filtros.</td></tr>
@endforelse

</tbody></table></div><div class="pagination">{{ $presupuestos->links('vendor.pagination.simple') }}</div></section>
@endsection
