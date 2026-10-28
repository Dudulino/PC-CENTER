<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Cliente extends Model
{
 protected $table='clientes';
 protected $fillable=['documento','nombre','correo','telefono'];
 public function presupuestos(){return $this->hasMany(Presupuesto::class,'cliente_id');}
}
