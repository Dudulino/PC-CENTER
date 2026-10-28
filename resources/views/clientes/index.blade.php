@extends('layouts.app')
@section('title','Clientes')
@section('breadcrumb','Mantenimiento / Clientes')
@section('content')
<div class="page-head"><div><span class="eyebrow ink">MANTENIMIENTO</span><h1>Clientes</h1><p>Datos necesarios para elaborar y recuperar presupuestos.</p></div><button class="btn primary" onclick="abrirCliente()">＋ Registrar cliente</button></div>
<section class="panel"><div class="panel-toolbar"><form class="searchform" method="get"><input name="q" value="{{ request('q') }}" placeholder="Buscar por cliente o documento" aria-label="Buscar cliente"><button class="btn secondary">Buscar</button></form><span class="muted">{{ $clientes->total() }} registros</span></div><div class="table-scroll"><table><thead><tr><th>Nombre / razón social</th><th>Documento</th><th>Correo</th><th>Teléfono</th><th></th></tr></thead><tbody>
@forelse($clientes as $c)
<tr><td class="strong">{{ $c->nombre }}</td><td>{{ $c->documento??'—' }}</td><td>{{ $c->correo??'—' }}</td><td>{{ $c->telefono??'—' }}</td><td><button class="btn link-btn js-edit-client" data-id="{{ $c->id }}" data-nombre="{{ $c->nombre }}" data-documento="{{ $c->documento }}" data-correo="{{ $c->correo }}" data-telefono="{{ $c->telefono }}">Editar</button></td></tr>@empty
<tr><td colspan="5" class="empty-cell">No hay clientes que coincidan.</td></tr>
@endforelse

</tbody></table></div><div class="pagination">{{ $clientes->links('vendor.pagination.simple') }}</div></section>
<dialog class="modal" id="clienteDialog"><form method="post" id="clienteForm" action="{{ route('clientes.store') }}">@csrf<input type="hidden" name="_method" id="clienteMethod" value="POST"><div class="modal-head"><h2 id="clienteTitle">Registrar cliente</h2><button class="xclose" type="button" onclick="document.getElementById('clienteDialog').close()">×</button></div><div class="modal-body"><label>Nombre / razón social <input id="cNombre" name="nombre" maxlength="180" required></label><div class="form-grid"><label>Documento (opcional) <input id="cDocumento" name="documento" maxlength="20"></label><label>Teléfono <input id="cTelefono" name="telefono" maxlength="30"></label></div><label>Correo electrónico <input id="cCorreo" name="correo" type="email" maxlength="254"></label></div><div class="modal-actions"><button class="btn secondary" type="button" onclick="document.getElementById('clienteDialog').close()">Cancelar</button><button class="btn primary">Guardar cliente</button></div></form></dialog>
<script>
function abrirCliente(){const f=document.getElementById('clienteForm');f.action=@json(route('clientes.store'));f.reset();document.getElementById('clienteMethod').value='POST';document.getElementById('clienteTitle').textContent='Registrar cliente';document.getElementById('clienteDialog').showModal()}
document.querySelectorAll('.js-edit-client').forEach(b=>b.addEventListener('click',()=>{const f=document.getElementById('clienteForm');f.action=@json(url('/clientes'))+'/'+b.dataset.id;document.getElementById('clienteMethod').value='PUT';document.getElementById('cNombre').value=b.dataset.nombre;document.getElementById('cDocumento').value=b.dataset.documento;document.getElementById('cCorreo').value=b.dataset.correo;document.getElementById('cTelefono').value=b.dataset.telefono;document.getElementById('clienteTitle').textContent='Editar cliente';document.getElementById('clienteDialog').showModal()}));
</script>
@endsection
