<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HistorialEstado extends Model
{
 public const MOTIVO_VENCIMIENTO_AUTOMATICO='Vencimiento automático: se superó la fecha de vigencia';
 protected $table='historial_estados';
 protected $fillable=['presupuesto_id','usuario_id','estado_anterior','estado_nuevo','motivo'];
 public function usuario(){return $this->belongsTo(Usuario::class,'usuario_id');}
 /** Cambios hechos por el propio sistema (no por una persona). */
 public function esAutomatico(): bool {return $this->motivo===self::MOTIVO_VENCIMIENTO_AUTOMATICO;}
}
