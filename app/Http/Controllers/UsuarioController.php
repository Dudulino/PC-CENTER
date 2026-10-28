<?php
namespace App\Http\Controllers;
use App\Models\Usuario;
use App\Support\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
class UsuarioController
{
 public function index(){return view('usuarios.index',['usuarios'=>Usuario::orderBy('nombre')->get()]);}
 private function clave(bool $obligatoria): array {
  return [$obligatoria?'required':'nullable','string',Password::min(8)->letters()->numbers()];
 }
 public function store(Request $r){
  $r->merge(['correo'=>is_string($r->input('correo'))?mb_strtolower(trim($r->input('correo'))):$r->input('correo')]);
  $d=$r->validate(['nombre'=>['required','string','max:120'],'correo'=>['required','email','max:254','unique:usuarios,correo'],'password'=>$this->clave(true),'rol'=>['required','in:administrador,atencion']]);
  $u=Usuario::create(['nombre'=>trim($d['nombre']),'correo'=>$d['correo'],'clave_hash'=>Hash::make($d['password']),'rol'=>$d['rol'],'activo'=>true]);
  Bitacora::registrar('usuario.creado',['usuario_creado'=>$u->id,'rol'=>$u->rol]);
  return back()->with('ok','Usuario creado.');
 }
 public function update(Request $r,Usuario $usuario){
  $r->merge(['correo'=>is_string($r->input('correo'))?mb_strtolower(trim($r->input('correo'))):$r->input('correo')]);
  $d=$r->validate(['nombre'=>['required','string','max:120'],'correo'=>['required','email','max:254',Rule::unique('usuarios','correo')->ignore($usuario->id)],'rol'=>['required','in:administrador,atencion'],'activo'=>['required','boolean'],'password'=>$this->clave(false)]);
  if($usuario->id===auth()->id()&&(!$d['activo']||$d['rol']!=='administrador'))return back()->withErrors(['activo'=>'No puedes desactivar tu propia cuenta ni quitarte el rol de administrador.']);
  $cambioPermisos=$usuario->rol!==$d['rol']||(bool)$usuario->activo!==(bool)$d['activo'];
  $usuario->update(['nombre'=>trim($d['nombre']),'correo'=>$d['correo'],'rol'=>$d['rol'],'activo'=>$d['activo']]+(!empty($d['password'])?['clave_hash'=>Hash::make($d['password'])]:[]));
  Bitacora::registrar('usuario.actualizado',['usuario_editado'=>$usuario->id,'rol'=>$usuario->rol,'activo'=>(bool)$usuario->activo,'cambio_permisos'=>$cambioPermisos,'clave_cambiada'=>!empty($d['password'])]);
  return back()->with('ok','Usuario actualizado.');
 }
}
