<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('usuarios',function(Blueprint $t){$t->id();$t->string('nombre',120);$t->string('correo',254)->unique();$t->string('clave_hash');$t->enum('rol',['administrador','atencion']);$t->boolean('activo')->default(true);$t->rememberToken();$t->timestamps();});
  Schema::create('clientes',function(Blueprint $t){$t->id();$t->string('documento',20)->nullable()->unique();$t->string('nombre',180);$t->string('correo',254)->nullable();$t->string('telefono',30)->nullable();$t->timestamps();});
  Schema::create('productos',function(Blueprint $t){$t->id();$t->string('codigo',64)->unique();$t->string('descripcion',255);$t->decimal('precio',12,2);$t->boolean('activo')->default(true);$t->timestamps();});
 }
 public function down(): void {Schema::dropIfExists('productos');Schema::dropIfExists('clientes');Schema::dropIfExists('usuarios');}
};
