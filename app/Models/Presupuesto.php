<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Presupuesto extends Model
{
 /** Estados finales: una vez alcanzados, el documento ya no cambia. */
 public const ESTADOS=['borrador','emitido','aceptado','rechazado','vencido','anulado'];
 public const DIAS_AVISO_VENCIMIENTO=3;

 protected $table='presupuestos';
 protected $fillable=['numero','cliente_id','usuario_id','fecha','vigencia','estado','moneda','total'];
 protected $casts=['fecha'=>'date:Y-m-d','vigencia'=>'date:Y-m-d','total'=>'decimal:2'];
 public function cliente(){return $this->belongsTo(Cliente::class,'cliente_id');}
 public function usuario(){return $this->belongsTo(Usuario::class,'usuario_id');}
 public function detalles(){return $this->hasMany(Detalle::class,'presupuesto_id');}
 public function historial(){return $this->hasMany(HistorialEstado::class,'presupuesto_id')->orderBy('id','desc');}

 /** La oferta ya superó su fecha de vigencia (sin importar el estado guardado). */
 public function vigenciaSuperada(): bool {return $this->vigencia!==null && $this->vigencia->lt(now()->startOfDay());}
 /** Días que faltan para el fin de la vigencia (0 = vence hoy, negativo = ya venció). */
 public function diasRestantes(): int {return (int) now()->startOfDay()->diffInDays($this->vigencia->copy()->startOfDay(), false);}
 public function porVencer(): bool {return $this->estado==='emitido' && !$this->vigenciaSuperada() && $this->diasRestantes()<=self::DIAS_AVISO_VENCIMIENTO;}
 public function esFinal(): bool {return in_array($this->estado,['aceptado','rechazado','vencido','anulado'],true);}

 /** Estados a los que puede pasar hoy un presupuesto emitido, según la vigencia (tesina 4.2). */
 public function transicionesPermitidas(): array {
  if($this->estado!=='emitido')return [];
  return $this->vigenciaSuperada()?['vencido','rechazado','anulado']:['aceptado','rechazado','anulado'];
 }
}
