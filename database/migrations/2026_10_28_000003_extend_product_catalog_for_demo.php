<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('productos', function (Blueprint $t) {
            $t->string('categoria', 60)->default('Otros')->index();
            $t->string('sku', 64)->nullable()->unique();
            $t->decimal('costo', 12, 2)->default(0);
            $t->unsignedInteger('stock')->nullable();
            $t->unsignedInteger('stock_minimo')->default(5);
            $t->boolean('es_servicio')->default(false);
            $t->string('icono', 20)->default('📦');
        });
    }
    public function down(): void {
        Schema::table('productos', function (Blueprint $t) {
            $t->dropIndex(['categoria']);
            $t->dropUnique(['sku']);
            $t->dropColumn(['categoria','sku','costo','stock','stock_minimo','es_servicio','icono']);
        });
    }
};
