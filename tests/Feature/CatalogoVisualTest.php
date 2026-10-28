<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Producto,Usuario,Presupuesto};

class CatalogoVisualTest extends TestCase
{
 use RefreshDatabase;
 public function test_la_precarga_es_idempotente(): void {
  $this->seed();
  $cuantos=Producto::count();$ejemplos=Presupuesto::where('numero','like','DEMO-%')->count();
  $this->seed();
  $this->assertSame($cuantos,Producto::count());
  $this->assertSame($ejemplos,Presupuesto::where('numero','like','DEMO-%')->count());
  $this->assertGreaterThanOrEqual(38,$cuantos);
  $this->assertSame(3,$ejemplos);
 }
 public function test_el_catalogo_visualmente_muestra_articulos_y_stock(): void {
  $this->seed();
  $u=Usuario::where('correo','admin@pccenter.test')->firstOrFail();
  $this->actingAs($u)->get(route('productos.index',['vista'=>'tarjetas']))
   ->assertOk()->assertSee('Productos y existencias')->assertSee('SSD Kingston NV3')->assertSee('Stock bajo');
 }
 public function test_presupuesto_muestra_catalogo_precargado(): void {
  $this->seed();
  $u=Usuario::where('correo','admin@pccenter.test')->firstOrFail();
  $this->actingAs($u)->get(route('presupuestos.create'))
   ->assertOk()->assertSee('posCatalogGrid')->assertSee('000123450001')->assertSee('Total cotizado');
 }
 public function test_no_se_actualizan_precios_del_usuario_al_resembrar(): void {
  $this->seed();
  $p=Producto::where('codigo','000123450001')->firstOrFail();
  $p->update(['precio'=>711.35]);
  $this->seed();
  $this->assertSame('711.35',$p->fresh()->precio);
 }
}
