<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('presupuestos',function(Blueprint $t){$t->id();$t->string('numero',30)->nullable()->unique();$t->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete();$t->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete();$t->date('fecha');$t->date('vigencia');$t->enum('estado',['borrador','emitido','aceptado','rechazado','vencido','anulado'])->default('borrador');$t->char('moneda',3)->default('PEN');$t->decimal('total',12,2)->default(0);$t->timestamps();$t->index(['estado','fecha']);});
  Schema::create('detalles',function(Blueprint $t){$t->id();$t->foreignId('presupuesto_id')->constrained('presupuestos')->cascadeOnDelete();$t->foreignId('producto_id')->constrained('productos')->restrictOnDelete();$t->unsignedInteger('cantidad');$t->string('descripcion_snapshot',255);$t->decimal('precio_snapshot',12,2);$t->decimal('subtotal',12,2);$t->timestamps();$t->unique(['presupuesto_id','producto_id']);});
  Schema::create('historial_estados',function(Blueprint $t){$t->id();$t->foreignId('presupuesto_id')->constrained('presupuestos')->cascadeOnDelete();$t->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete();$t->string('estado_anterior',20)->nullable();$t->string('estado_nuevo',20);$t->string('motivo',255)->nullable();$t->timestamps();});
 }
 public function down(): void {Schema::dropIfExists('historial_estados');Schema::dropIfExists('detalles');Schema::dropIfExists('presupuestos');}
};
