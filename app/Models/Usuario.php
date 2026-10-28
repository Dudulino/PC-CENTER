<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
class Usuario extends Authenticatable
{
 protected $table='usuarios';
 protected $fillable=['nombre','correo','clave_hash','rol','activo'];
 protected $hidden=['clave_hash','remember_token'];
 protected $casts=['activo'=>'boolean'];
 public function getAuthPasswordName(): string {return 'clave_hash';}
 public function getAuthPassword(): string {return $this->clave_hash;}
 public function presupuestos(){return $this->hasMany(Presupuesto::class,'usuario_id');}
 public function esAdmin(): bool {return $this->rol==='administrador';}
}
