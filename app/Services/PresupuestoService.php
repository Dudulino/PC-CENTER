<?php
namespace App\Services;
use App\Models\{Presupuesto,Producto,Detalle,HistorialEstado};
use App\Support\Bitacora;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class PresupuestoService
{
 /** Reconstruye todos los importes en el servidor, ignorando precios enviados por el navegador. */
 public function guardar(array $datos, int $usuarioId, ?Presupuesto $existente=null): Presupuesto
 {
  $items=json_decode($datos['items']??'',true);
  if(!is_array($items)||count($items)<1||count($items)>100)throw ValidationException::withMessages(['items'=>'Agrega entre 1 y 100 productos.']);
  $agrupados=[];
  foreach($items as $item){
   if(!is_array($item)||!isset($item['producto_id'],$item['cantidad']))throw ValidationException::withMessages(['items'=>'Detalle de productos inválido.']);
   $id=filter_var($item['producto_id'],FILTER_VALIDATE_INT);$cantidad=filter_var($item['cantidad'],FILTER_VALIDATE_INT);
   if($id===false||$cantidad===false||$cantidad<1||$cantidad>100000)throw ValidationException::withMessages(['items'=>'Cada cantidad debe ser un entero positivo (máximo 100000).']);
   $agrupados[$id]=($agrupados[$id]??0)+$cantidad;
   if($agrupados[$id]>100000)throw ValidationException::withMessages(['items'=>'Cantidad total demasiado grande.']);
  }
  return DB::transaction(function() use($datos,$usuarioId,$existente,$agrupados){
    if($existente){
      $p=Presupuesto::whereKey($existente->id)->lockForUpdate()->firstOrFail();
      if($p->estado!=='borrador')throw ValidationException::withMessages(['items'=>'Solo se puede modificar un borrador.']);
      $p->update(['cliente_id'=>$datos['cliente_id'],'vigencia'=>$datos['vigencia']]);
      $p->detalles()->delete();
    }else{
      $p=Presupuesto::create(['cliente_id'=>$datos['cliente_id'],'usuario_id'=>$usuarioId,'fecha'=>now()->toDateString(),'vigencia'=>$datos['vigencia'],'estado'=>'borrador','moneda'=>'PEN','total'=>0]);
      HistorialEstado::create(['presupuesto_id'=>$p->id,'usuario_id'=>$usuarioId,'estado_anterior'=>null,'estado_nuevo'=>'borrador','motivo'=>'Creación del borrador']);
    }
    $productos=Producto::whereIn('id',array_keys($agrupados))->where('activo',true)->get()->keyBy('id');
    if($productos->count()!==count($agrupados))throw ValidationException::withMessages(['items'=>'Un producto no existe o está inactivo.']);
    $totalCentimos=0;
    foreach($agrupados as $id=>$cantidad){
      $producto=$productos[$id];
      $precioCentimos=(int)round(((float)$producto->precio)*100);
      $subCentimos=$precioCentimos*$cantidad;
      if($subCentimos>999999999999||$totalCentimos+$subCentimos>999999999999)throw ValidationException::withMessages(['items'=>'El importe supera el máximo admitido.']);
      $totalCentimos+=$subCentimos;
      Detalle::create(['presupuesto_id'=>$p->id,'producto_id'=>$producto->id,'cantidad'=>$cantidad,'descripcion_snapshot'=>$producto->descripcion,'precio_snapshot'=>number_format($precioCentimos/100,2,'.',''),'subtotal'=>number_format($subCentimos/100,2,'.','')]);
    }
    $p->update(['total'=>number_format($totalCentimos/100,2,'.','')]);
    return $p;
  },3);
 }
 public function emitir(Presupuesto $presupuesto, int $usuarioId):Presupuesto
 {
  return DB::transaction(function()use($presupuesto,$usuarioId){
   $p=Presupuesto::whereKey($presupuesto->id)->lockForUpdate()->firstOrFail();
   if($p->estado==='borrador'){
     if($p->detalles()->count()===0)throw ValidationException::withMessages(['items'=>'No hay productos para emitir.']);
     if($p->vigencia->lt(now()->startOfDay()))throw ValidationException::withMessages(['vigencia'=>'La vigencia está vencida.']);
     $p->update(['numero'=>sprintf('PRE-%s-%06d',$p->fecha->format('Y'),$p->id),'estado'=>'emitido']);
     HistorialEstado::create(['presupuesto_id'=>$p->id,'usuario_id'=>$usuarioId,'estado_anterior'=>'borrador','estado_nuevo'=>'emitido','motivo'=>'Emisión oficial del presupuesto']);
     Bitacora::registrar('presupuesto.emitido',['presupuesto_id'=>$p->id,'numero'=>$p->numero,'total'=>$p->total]);
   }elseif(!$p->numero){throw ValidationException::withMessages(['estado'=>'Este presupuesto no puede emitirse.']);}
   return $p;
  },3);
 }

 /**
  * Cambio comercial de un presupuesto emitido. Reglas (tesina 4.2):
  * - solo desde "emitido";
  * - "aceptado" exige oferta vigente y "vencido" exige vigencia superada;
  * - "rechazado" y "anulado" exigen un motivo para la trazabilidad.
  */
 public function cambiarEstado(Presupuesto $presupuesto, string $nuevo, ?string $motivo, int $usuarioId): Presupuesto
 {
  $motivo=trim((string)$motivo);
  return DB::transaction(function()use($presupuesto,$nuevo,$motivo,$usuarioId){
   $p=Presupuesto::whereKey($presupuesto->id)->lockForUpdate()->firstOrFail();
   if($p->estado!=='emitido')throw ValidationException::withMessages(['estado'=>'Solo un presupuesto emitido puede cambiar de estado.']);
   if(!in_array($nuevo,$p->transicionesPermitidas(),true)){
    $msg=match($nuevo){
     'aceptado'=>'No se puede aceptar un presupuesto cuya vigencia ya venció. Prepara uno nuevo.',
     'vencido'=>'El presupuesto aún está vigente: no puede marcarse como vencido.',
     default=>'Transición de estado no permitida.',
    };
    throw ValidationException::withMessages(['estado'=>$msg]);
   }
   if(in_array($nuevo,['rechazado','anulado'],true)&&mb_strlen($motivo)<5)throw ValidationException::withMessages(['motivo'=>'Indica el motivo (mínimo 5 caracteres) para rechazar o anular el presupuesto.']);
   $anterior=$p->estado;$p->update(['estado'=>$nuevo]);
   HistorialEstado::create(['presupuesto_id'=>$p->id,'usuario_id'=>$usuarioId,'estado_anterior'=>$anterior,'estado_nuevo'=>$nuevo,'motivo'=>$motivo!==''?$motivo:'Actualización comercial']);
   Bitacora::registrar('presupuesto.estado',['presupuesto_id'=>$p->id,'numero'=>$p->numero,'de'=>$anterior,'a'=>$nuevo]);
   return $p;
  },3);
 }

 /** Pasa a "vencido" todo presupuesto emitido cuya vigencia ya terminó. Devuelve cuántos cambió. */
 public function vencerVigencias(): int
 {
  $ids=Presupuesto::where('estado','emitido')->whereDate('vigencia','<',now()->toDateString())->pluck('id');
  $cambiados=0;
  foreach($ids as $id){
   DB::transaction(function()use($id,&$cambiados){
    $p=Presupuesto::whereKey($id)->lockForUpdate()->first();
    if(!$p||$p->estado!=='emitido'||!$p->vigenciaSuperada())return;
    $p->update(['estado'=>'vencido']);
    HistorialEstado::create(['presupuesto_id'=>$p->id,'usuario_id'=>$p->usuario_id,'estado_anterior'=>'emitido','estado_nuevo'=>'vencido','motivo'=>HistorialEstado::MOTIVO_VENCIMIENTO_AUTOMATICO]);
    $cambiados++;
   },3);
  }
  if($cambiados>0)Bitacora::registrar('presupuesto.vencimiento_automatico',['cantidad'=>$cambiados]);
  return $cambiados;
 }

 /**
  * Un cambio comercial requiere un presupuesto nuevo: copia cliente y cantidades a un borrador
  * con los precios VIGENTES del catálogo. Los productos inactivos se omiten.
  * @return array{0:Presupuesto,1:int} borrador creado y cantidad de productos omitidos
  */
 public function duplicar(Presupuesto $origen, int $usuarioId): array
 {
  $origen->loadMissing('detalles.producto');
  $items=[];$omitidos=0;
  foreach($origen->detalles as $d){
   if($d->producto&&$d->producto->activo)$items[]=['producto_id'=>$d->producto_id,'cantidad'=>$d->cantidad];
   else $omitidos++;
  }
  if(!$items)throw ValidationException::withMessages(['items'=>'Ningún producto del presupuesto original sigue activo en el catálogo.']);
  $nuevo=$this->guardar(['cliente_id'=>$origen->cliente_id,'vigencia'=>now()->addDays(7)->toDateString(),'items'=>json_encode($items)],$usuarioId);
  Bitacora::registrar('presupuesto.duplicado',['origen_id'=>$origen->id,'nuevo_id'=>$nuevo->id]);
  return [$nuevo,$omitidos];
 }
}
