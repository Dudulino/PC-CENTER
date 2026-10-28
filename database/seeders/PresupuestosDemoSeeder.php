<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\{Usuario,Cliente,Producto,Presupuesto,Detalle,HistorialEstado};

/** Documentos totalmente ficticios, claramente marcados DEMO; no son ventas ni boletas. */
class PresupuestosDemoSeeder extends Seeder
{
 public function run(): void {
  $usuario=Usuario::where('correo','admin@pccenter.test')->first();
  if(!$usuario) return;
  $ejemplos=[
   ['DEMO-PRE-2026-001','20100000001','emitido',[['000123450001',2],['000123450002',2],['000123450003',1]],3],
   ['DEMO-PRE-2026-002','00000001','aceptado',[['000123450005',1],['000123450004',1],['000123450006',1]],5],
   ['DEMO-PRE-2026-003','20100000003','emitido',[['000123450013',2],['000123450022',2],['000123450035',1]],1],
  ];
  foreach($ejemplos as [$numero,$documento,$estado,$items,$dias]){
   if(Presupuesto::where('numero',$numero)->exists())continue;
   $cliente=Cliente::where('documento',$documento)->first();if(!$cliente)continue;
   DB::transaction(function() use ($numero,$cliente,$usuario,$estado,$items,$dias) {
    $fecha=now()->subDays($dias)->toDateString();
    $p=Presupuesto::create(['numero'=>$numero,'cliente_id'=>$cliente->id,'usuario_id'=>$usuario->id,'fecha'=>$fecha,'vigencia'=>now()->addDays(14)->toDateString(),'estado'=>$estado,'moneda'=>'PEN','total'=>0]);
    $total=0;
    foreach($items as [$codigo,$cantidad]) {
     $prod=Producto::where('codigo',$codigo)->first(); if(!$prod) continue;
     $precio=(int) round(((float)$prod->precio)*100);
     $subtotal=$precio*$cantidad;$total+=$subtotal;
     Detalle::create(['presupuesto_id'=>$p->id,'producto_id'=>$prod->id,'cantidad'=>$cantidad,'descripcion_snapshot'=>$prod->descripcion,'precio_snapshot'=>number_format($precio/100,2,'.',''),'subtotal'=>number_format($subtotal/100,2,'.','')]);
    }
    $p->update(['total'=>number_format($total/100,2,'.','')]);
    HistorialEstado::create(['presupuesto_id'=>$p->id,'usuario_id'=>$usuario->id,'estado_anterior'=>null,'estado_nuevo'=>'borrador','motivo'=>'Creación ficticia de demostración']);
    HistorialEstado::create(['presupuesto_id'=>$p->id,'usuario_id'=>$usuario->id,'estado_anterior'=>'borrador','estado_nuevo'=>'emitido','motivo'=>'Documento de ejemplo precargado']);
    if($estado==='aceptado') HistorialEstado::create(['presupuesto_id'=>$p->id,'usuario_id'=>$usuario->id,'estado_anterior'=>'emitido','estado_nuevo'=>'aceptado','motivo'=>'Aceptación simulada para demostración']);
   });
  }
 }
}
