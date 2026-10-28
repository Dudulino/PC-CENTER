<?php
namespace App\Http\Controllers;
use App\Support\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
class RespaldoController
{
 private array $tablas=['usuarios','clientes','productos','presupuestos','detalles','historial_estados'];
 public function index(){return view('respaldo.index');}
 public function descargar(){
  $data=['formato'=>'pc-center-backup-v1','generado'=>now()->toIso8601String(),'tablas'=>[]];
  foreach($this->tablas as $t)$data['tablas'][$t]=DB::table($t)->orderBy('id')->get()->map(fn($x)=>(array)$x)->all();
  Bitacora::registrar('respaldo.descargado');
  $json=json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
  return response($json,200,['Content-Type'=>'application/json','Content-Disposition'=>'attachment; filename="PC_Center_respaldo_'.date('Ymd_His').'.json"','Cache-Control'=>'no-store']);
 }
 public function restaurar(Request $r){
  $r->validate(['archivo'=>['required','file','mimes:json,txt','max:10240'],'confirmacion'=>['required','in:RESTAURAR']]);
  try{$json=json_decode(file_get_contents($r->file('archivo')->getRealPath()),true,512,JSON_THROW_ON_ERROR);}catch(\Throwable){throw ValidationException::withMessages(['archivo'=>'El archivo no contiene JSON válido.']);}
  if(($json['formato']??null)!=='pc-center-backup-v1'||!is_array($json['tablas']??null))throw ValidationException::withMessages(['archivo'=>'Respaldo incompatible con PC Center.']);
  foreach($this->tablas as $t){if(!isset($json['tablas'][$t])||!is_array($json['tablas'][$t])||count($json['tablas'][$t])>50000)throw ValidationException::withMessages(['archivo'=>'La tabla '.$t.' es inválida o demasiado grande.']);}
  if(!collect($json['tablas']['usuarios'])->contains(fn($u)=>is_array($u)&&($u['rol']??'')==='administrador'&&($u['activo']??0)))throw ValidationException::withMessages(['archivo'=>'Se necesita al menos un administrador activo.']);
  DB::transaction(function()use($json){
   foreach(array_reverse($this->tablas) as $t)DB::table($t)->delete();
   foreach($this->tablas as $t){
     $allowed=DB::getSchemaBuilder()->getColumnListing($t);
     foreach(array_chunk($json['tablas'][$t],100) as $bloque){
       $filas=[];
       foreach($bloque as $f){if(!is_array($f)||!isset($f['id'])||array_diff(array_keys($f),$allowed))throw new \RuntimeException('Columnas incompatibles en '.$t);$filas[]=$f;}
       DB::table($t)->insert($filas);
     }
   }
  });
  Bitacora::registrar('respaldo.restaurado');
  Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();
  return redirect()->route('login')->with('ok','Respaldo restaurado. Inicia sesión con los usuarios del archivo.');
 }
}
