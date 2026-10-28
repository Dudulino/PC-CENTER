<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Detalle extends Model
{
 protected $table='detalles';
 protected $fillable=['presupuesto_id','producto_id','cantidad','descripcion_snapshot','precio_snapshot','subtotal'];
 protected $casts=['precio_snapshot'=>'decimal:2','subtotal'=>'decimal:2'];
 public function producto(){return $this->belongsTo(Producto::class,'producto_id');}
}
