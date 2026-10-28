<?php
namespace App\Http\Controllers;
use App\Models\Cliente;
use App\Support\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class ClienteController
{
 public function index(Request $r){
  $term=trim((string)$r->input('q'));
  $clientes=Cliente::query()->when($term!=='',fn($q)=>$q->where(function($w)use($term){$w->where('nombre','like','%'.$term.'%')->orWhere('documento','like','%'.$term.'%');}))->orderBy('nombre')->paginate(12)->withQueryString();
  return view('clientes.index',compact('clientes'));
 }
 /** DNI (8 dígitos), RUC (11 dígitos que empiezan en 10, 15, 16, 17 o 20) o CE / pasaporte (9 a 12 letras o números). */
 private function documentoValido(string $v): bool {
  if(preg_match('/^\d{8}$/',$v))return true;
  if(preg_match('/^(10|15|16|17|20)\d{9}$/',$v))return true;
  return (bool)preg_match('/^[A-Z0-9]{9,12}$/',$v) && !preg_match('/^\d{11}$/',$v);
 }
 private function limpiar(Request $r): void {
  $doc=$r->input('documento');
  $r->merge([
   'documento'=>is_string($doc)&&trim($doc)!==''?strtoupper(preg_replace('/[\s\-]/','',$doc)):null,
   'nombre'=>is_string($r->input('nombre'))?trim(preg_replace('/\s+/',' ',$r->input('nombre'))):$r->input('nombre'),
   'correo'=>is_string($r->input('correo'))?mb_strtolower(trim($r->input('correo'))):$r->input('correo'),
  ]);
 }
 private function reglas(?Cliente $c=null):array {
  return [
   'documento'=>['nullable','string','max:20',Rule::unique('clientes','documento')->ignore($c?->id),function($attr,$value,$fail){if($value!==null&&!$this->documentoValido($value))$fail('El documento debe ser un DNI (8 dígitos), un RUC (11 dígitos válidos) o un CE/pasaporte (9 a 12 caracteres).');}],
   'nombre'=>['required','string','min:3','max:180'],
   'correo'=>['nullable','email','max:254'],
   'telefono'=>['nullable','string','max:30','regex:/^[0-9+()\-\s]{6,30}$/'],
  ];
 }
 public function store(Request $r){
  $this->limpiar($r);
  $c=Cliente::create($r->validate($this->reglas()));
  Bitacora::registrar('cliente.creado',['cliente_id'=>$c->id]);
  if($r->wantsJson())return response()->json($c,201);
  return back()->with('ok','Cliente registrado.');
 }
 public function update(Request $r,Cliente $cliente){
  $this->limpiar($r);
  $cliente->update($r->validate($this->reglas($cliente)));
  Bitacora::registrar('cliente.actualizado',['cliente_id'=>$cliente->id]);
  return back()->with('ok','Cliente actualizado.');
 }
}
