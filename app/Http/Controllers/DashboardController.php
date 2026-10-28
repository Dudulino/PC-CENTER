<?php
namespace App\Http\Controllers;
use App\Models\{Producto,Cliente,Presupuesto};
use App\Services\PresupuestoService;
class DashboardController
{
 public function index(PresupuestoService $servicio){
  $servicio->vencerVigencias();
  $totales=['productos'=>Producto::where('activo',true)->count(),'clientes'=>Cliente::count(),'presupuestos'=>Presupuesto::count(),'emitidos'=>Presupuesto::where('estado','emitido')->count()];
  $monto=Presupuesto::whereIn('estado',['emitido','aceptado'])->sum('total');
  $recientes=Presupuesto::with(['cliente','usuario'])->latest('id')->take(7)->get();
  $estados=Presupuesto::selectRaw('estado, COUNT(*) as cantidad')->groupBy('estado')->pluck('cantidad','estado');

  // Importe emitido/aceptado de los últimos 6 meses (agrupado en PHP para ser compatible con MySQL y SQLite).
  $desde=now()->subMonths(5)->startOfMonth();
  $porMes=Presupuesto::whereIn('estado',['emitido','aceptado'])->where('fecha','>=',$desde->toDateString())->get(['fecha','total'])
   ->groupBy(fn($p)=>$p->fecha->format('Y-m'))->map(fn($g)=>['cantidad'=>$g->count(),'importe'=>(float)$g->sum('total')]);
  $meses=[];
  for($i=0;$i<6;$i++){$m=$desde->copy()->addMonths($i);$k=$m->format('Y-m');$meses[]=['etiqueta'=>$m->locale('es')->translatedFormat('M y'),'cantidad'=>$porMes[$k]['cantidad']??0,'importe'=>$porMes[$k]['importe']??0];}
  $maxMes=max(1,max(array_column($meses,'importe')));

  $porVencer=Presupuesto::with('cliente')->where('estado','emitido')->whereDate('vigencia','>=',now()->toDateString())
   ->whereDate('vigencia','<=',now()->addDays(Presupuesto::DIAS_AVISO_VENCIMIENTO)->toDateString())->orderBy('vigencia')->take(5)->get();
  $stockBajo=Producto::where('activo',true)->where('es_servicio',false)->whereNotNull('stock')->whereColumn('stock','<=','stock_minimo')->orderBy('stock')->take(5)->get(['id','descripcion','stock','stock_minimo']);
  $misBorradores=Presupuesto::where('estado','borrador')->where('usuario_id',auth()->id())->count();
  return view('dashboard',compact('totales','monto','recientes','estados','meses','maxMes','porVencer','stockBajo','misBorradores'));
 }
}
