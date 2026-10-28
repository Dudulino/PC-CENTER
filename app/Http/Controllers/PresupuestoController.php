<?php
namespace App\Http\Controllers;
use App\Models\{Presupuesto,Cliente,Producto};
use App\Services\PresupuestoService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;
class PresupuestoController
{
 public function index(Request $r,PresupuestoService $s){
  $s->vencerVigencias();
  $q=Presupuesto::with(['cliente','usuario'])->when($r->filled('estado'),fn($b)=>$b->where('estado',$r->estado))
     ->when($r->filled('fecha'),fn($b)=>$b->whereDate('fecha',$r->fecha))
     ->when($r->filled('q'),fn($b)=>$b->where(function($x)use($r){$x->where('numero','like','%'.$r->q.'%')->orWhereHas('cliente',fn($c)=>$c->where('nombre','like','%'.$r->q.'%'));}));
  $presupuestos=$q->orderByDesc('id')->paginate(12)->withQueryString();
  return view('presupuestos.index',compact('presupuestos'));
 }
 public function create(){return $this->formulario(null);}
 public function edit(Presupuesto $presupuesto){
  abort_unless($presupuesto->estado==='borrador',403,'Solo se puede editar un borrador. Duplica el presupuesto para hacer cambios.');
  $presupuesto->load('detalles.producto');
  return $this->formulario($presupuesto);
 }
 private function formulario(?Presupuesto $presupuesto){
  $catalogo=Producto::where('activo',true)->orderBy('categoria')->orderBy('descripcion')->get();
  $categorias=$catalogo->pluck('categoria')->unique()->values();
  return view('presupuestos.form',[
   'presupuesto'=>$presupuesto,'clientes'=>Cliente::orderBy('nombre')->get(),
   'catalogo'=>$catalogo,'categorias'=>$categorias,
  ]);
 }
 private function datos(Request $r):array {
  return $r->validate(['cliente_id'=>['required','integer','exists:clientes,id'],'vigencia'=>['required','date','after_or_equal:today'],'items'=>['required','json']]);
 }
 public function store(Request $r,PresupuestoService $s){$p=$s->guardar($this->datos($r),auth()->id());return redirect()->route('presupuestos.show',$p)->with('ok','Borrador guardado con totales calculados en servidor.');}
 public function update(Request $r,Presupuesto $presupuesto,PresupuestoService $s){$p=$s->guardar($this->datos($r),auth()->id(),$presupuesto);return redirect()->route('presupuestos.show',$p)->with('ok','Borrador actualizado correctamente.');}
 public function show(Presupuesto $presupuesto,PresupuestoService $s){
  $s->vencerVigencias();
  $presupuesto->refresh()->load(['cliente','usuario','detalles','historial.usuario']);
  return view('presupuestos.show',['presupuesto'=>$presupuesto,'transiciones'=>$presupuesto->transicionesPermitidas()]);
 }
 public function emitir(Presupuesto $presupuesto,PresupuestoService $s){
  $yaEmitido=$presupuesto->estado!=='borrador';
  try{$p=$s->emitir($presupuesto,auth()->id());}
  catch(ValidationException $e){return back()->withErrors($e->errors());}
  return redirect()->route('presupuestos.show',$p)->with('ok',$yaEmitido?'Este presupuesto ya estaba emitido; no se generó un número nuevo.':'Presupuesto emitido. Ya puedes descargar el PDF.');
 }
 public function estado(Request $r,Presupuesto $presupuesto,PresupuestoService $s){
  $v=$r->validate(['estado'=>['required','in:aceptado,rechazado,vencido,anulado'],'motivo'=>['nullable','string','max:255']]);
  $s->cambiarEstado($presupuesto,$v['estado'],$v['motivo']??null,auth()->id());
  return back()->with('ok','Estado actualizado y registrado en el historial.');
 }
 public function duplicar(Presupuesto $presupuesto,PresupuestoService $s){
  abort_if($presupuesto->estado==='borrador',403,'Un borrador se edita directamente; no necesita duplicarse.');
  [$nuevo,$omitidos]=$s->duplicar($presupuesto,auth()->id());
  $aviso='Se creó un borrador nuevo con los precios vigentes del catálogo.'.($omitidos?" Se omitieron {$omitidos} producto(s) inactivo(s).":'');
  return redirect()->route('presupuestos.edit',$nuevo)->with('ok',$aviso);
 }
 public function pdf(Presupuesto $presupuesto){
  abort_if($presupuesto->estado==='borrador'||!$presupuesto->numero,403,'Primero debe emitir el presupuesto.');
  $presupuesto->load(['cliente','usuario','detalles.producto']);
  return Pdf::loadView('presupuestos.pdf',compact('presupuesto'))->setPaper('a4')->download($presupuesto->numero.'.pdf');
 }
}
