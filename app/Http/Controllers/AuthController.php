<?php
namespace App\Http\Controllers;
use App\Support\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthController
{
 public function form(){ if(Auth::check())return redirect()->route('panel');return view('auth.login'); }
 public function login(Request $request){
  $datos=$request->validate(['correo'=>['required','email'],'password'=>['required','string']]);
  $correo=mb_strtolower(trim($datos['correo']));
  if(!Auth::attempt(['correo'=>$correo,'password'=>$datos['password'],'activo'=>1])){
   Bitacora::registrar('auth.login_fallido',['correo'=>$correo]); // nunca se registra la contraseña
   return back()->withErrors(['correo'=>'Credenciales incorrectas o usuario desactivado.'])->onlyInput('correo');
  }
  $request->session()->regenerate();
  Bitacora::registrar('auth.login',['correo'=>$correo]);
  return redirect()->intended(route('panel'));
 }
 public function logout(Request $request){
  Bitacora::registrar('auth.logout');
  Auth::logout();$request->session()->invalidate();$request->session()->regenerateToken();return redirect()->route('login');
 }
}
