<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\{Cliente,Producto,Usuario,Presupuesto};
use App\Services\PresupuestoService;
class PresupuestosTest extends TestCase
{
 use RefreshDatabase;
 private function user(string $rol='administrador'):Usuario {
  return Usuario::create(['nombre'=>'Prueba','correo'=>$rol.'@test.local','clave_hash'=>Hash::make('abc12345'),'rol'=>$rol,'activo'=>true]);
 }
 private function cliente():Cliente {return Cliente::create(['documento'=>'10000001','nombre'=>'Cliente prueba']);}
 private function producto(string $codigo,string $precio):Producto {return Producto::create(['codigo'=>$codigo,'descripcion'=>'Producto '.$codigo,'precio'=>$precio,'activo'=>true]);}
 public function test_precio_calculado_en_servidor_y_total_correcto():void {
  $u=$this->user();$c=$this->cliente();$a=$this->producto('000150','150.00');$b=$this->producto('00001250','12.50');
  $p=app(PresupuestoService::class)->guardar(['cliente_id'=>$c->id,'vigencia'=>now()->addDays(7)->toDateString(),'items'=>json_encode([['producto_id'=>$a->id,'cantidad'=>2],['producto_id'=>$b->id,'cantidad'=>3]])],$u->id);
  $this->assertSame('337.50',$p->fresh()->total);
  $this->assertDatabaseHas('detalles',['presupuesto_id'=>$p->id,'cantidad'=>2,'subtotal'=>'300.00']);
 }
 public function test_escaneo_codigo_inexistente_devuelve_404():void {
  $this->actingAs($this->user())->getJson(route('productos.codigo',['codigo'=>'NO-EXISTE']))->assertNotFound();
 }
 public function test_emision_repetida_no_duplica_historial():void {
  $u=$this->user();$c=$this->cliente();$a=$this->producto('00010','20.00');$s=app(PresupuestoService::class);
  $p=$s->guardar(['cliente_id'=>$c->id,'vigencia'=>now()->addDay()->toDateString(),'items'=>json_encode([['producto_id'=>$a->id,'cantidad'=>1]])],$u->id);
  $s->emitir($p,$u->id);$s->emitir($p,$u->id);
  $this->assertSame(2,$p->historial()->count());$this->assertSame('emitido',$p->fresh()->estado);
 }
 public function test_importe_historico_no_cambia_si_se_modifica_catalogo():void {
  $u=$this->user();$c=$this->cliente();$a=$this->producto('00020','30.00');$s=app(PresupuestoService::class);
  $p=$s->guardar(['cliente_id'=>$c->id,'vigencia'=>now()->addDay()->toDateString(),'items'=>json_encode([['producto_id'=>$a->id,'cantidad'=>2]])],$u->id);
  $s->emitir($p,$u->id);$a->update(['precio'=>99]);
  $this->assertSame('30.00',$p->detalles()->first()->precio_snapshot);
  $this->assertSame('60.00',$p->fresh()->total);
 }
 public function test_atencion_no_puede_crear_productos():void {
  $u=$this->user('atencion');$this->actingAs($u)->post(route('productos.store'),['codigo'=>'X','descripcion'=>'Nuevo','precio'=>'10','activo'=>1])->assertForbidden();
 }
}
