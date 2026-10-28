<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\{Cliente,Producto,Usuario,Presupuesto,HistorialEstado};
use App\Services\PresupuestoService;

/** Pruebas de las reglas de negocio y de calidad añadidas en la versión 3.1. */
class MejorasTest extends TestCase
{
 use RefreshDatabase;
 private function user(string $rol='administrador'):Usuario {
  return Usuario::create(['nombre'=>'Prueba '.$rol,'correo'=>$rol.'@test.local','clave_hash'=>Hash::make('abc12345'),'rol'=>$rol,'activo'=>true]);
 }
 private function cliente():Cliente {return Cliente::create(['documento'=>'10000001','nombre'=>'Cliente prueba']);}
 private function producto(string $codigo='P-1',string $precio='30.00',array $extra=[]):Producto {
  return Producto::create(['codigo'=>$codigo,'descripcion'=>'Producto '.$codigo,'precio'=>$precio]+$extra+['activo'=>true]);
 }
 /** Presupuesto emitido con una línea, con la vigencia indicada (en días desde hoy). */
 private function emitido(int $diasVigencia,int $cantidad=2):Presupuesto {
  $u=Usuario::first()??$this->user();$c=Cliente::first()??$this->cliente();$prod=Producto::first()??$this->producto();
  $s=app(PresupuestoService::class);
  $p=$s->guardar(['cliente_id'=>$c->id,'vigencia'=>now()->addDays(max($diasVigencia,0))->toDateString(),'items'=>json_encode([['producto_id'=>$prod->id,'cantidad'=>$cantidad]])],$u->id);
  $s->emitir($p,$u->id);
  if($diasVigencia<0)$p->update(['vigencia'=>now()->addDays($diasVigencia)->toDateString()]);
  return $p->fresh();
 }

 public function test_el_formulario_de_presupuesto_no_muestra_directivas_blade_sin_procesar():void {
  $this->seed();
  $u=Usuario::where('correo','admin@pccenter.test')->firstOrFail();
  $this->actingAs($u)->get(route('presupuestos.create'))
   ->assertOk()->assertDontSee('@elseif',false)->assertDontSee('@if(',false)->assertSee('unidades ref.');
 }
 public function test_editar_usuario_usa_metodo_put():void {
  $this->seed();
  $u=Usuario::where('correo','admin@pccenter.test')->firstOrFail();
  $this->actingAs($u)->get(route('usuarios.index'))->assertOk()->assertDontSee("@method('PUT')",false)->assertSee('name="_method" value="PUT"',false);
 }
 public function test_los_mensajes_de_validacion_estan_en_espanol():void {
  $this->actingAs($this->user())->post(route('clientes.store'),['nombre'=>''])
   ->assertSessionHasErrors(['nombre'=>'El campo nombre es obligatorio.']);
 }
 public function test_vencimiento_automatico_de_presupuestos_emitidos():void {
  $this->user();$this->cliente();$this->producto();
  $vencido=$this->emitido(-2);$vigente=$this->emitido(10);
  $n=app(PresupuestoService::class)->vencerVigencias();
  $this->assertSame(1,$n);
  $this->assertSame('vencido',$vencido->fresh()->estado);
  $this->assertSame('emitido',$vigente->fresh()->estado);
  $ultimo=$vencido->historial()->first();
  $this->assertSame('vencido',$ultimo->estado_nuevo);$this->assertTrue($ultimo->esAutomatico());
  // Repetir no cambia nada ni duplica el historial.
  $this->assertSame(0,app(PresupuestoService::class)->vencerVigencias());
  $this->assertSame(3,$vencido->historial()->count());
 }
 public function test_no_se_puede_aceptar_una_oferta_vencida():void {
  $u=$this->user();$this->cliente();$this->producto();$p=$this->emitido(-1);
  $this->expectException(ValidationException::class);
  app(PresupuestoService::class)->cambiarEstado($p,'aceptado',null,$u->id);
 }
 public function test_no_se_puede_marcar_vencido_un_presupuesto_vigente():void {
  $u=$this->user();$this->cliente();$this->producto();$p=$this->emitido(5);
  $this->expectException(ValidationException::class);
  app(PresupuestoService::class)->cambiarEstado($p,'vencido',null,$u->id);
 }
 public function test_rechazar_o_anular_exige_motivo():void {
  $u=$this->user();$this->cliente();$this->producto();$p=$this->emitido(5);
  try{app(PresupuestoService::class)->cambiarEstado($p,'anulado','x',$u->id);$this->fail('Debió exigir un motivo.');}
  catch(ValidationException $e){$this->assertArrayHasKey('motivo',$e->errors());}
  $r=app(PresupuestoService::class)->cambiarEstado($p,'anulado','Cliente canceló la compra',$u->id);
  $this->assertSame('anulado',$r->estado);
 }
 public function test_un_estado_final_no_vuelve_a_cambiar():void {
  $u=$this->user();$this->cliente();$this->producto();$p=$this->emitido(5);
  $s=app(PresupuestoService::class);$s->cambiarEstado($p,'aceptado','Aprobado por el cliente',$u->id);
  $this->expectException(ValidationException::class);
  $s->cambiarEstado($p,'anulado','Intento tardío de anulación',$u->id);
 }
 public function test_atencion_no_puede_cambiar_estado_comercial():void {
  $this->user();$this->cliente();$this->producto();$p=$this->emitido(5);
  $this->actingAs($this->user('atencion'))->post(route('presupuestos.estado',$p),['estado'=>'aceptado'])->assertForbidden();
 }
 public function test_duplicar_crea_borrador_con_precios_vigentes_y_no_toca_el_original():void {
  $u=$this->user();$this->cliente();$prod=$this->producto('P-9','30.00');$orig=$this->emitido(5,2);
  $prod->update(['precio'=>'50.00']);
  [$nuevo,$omitidos]=app(PresupuestoService::class)->duplicar($orig,$u->id);
  $this->assertSame(0,$omitidos);
  $this->assertSame('borrador',$nuevo->estado);$this->assertSame('100.00',$nuevo->fresh()->total);
  $this->assertSame('60.00',$orig->fresh()->total);$this->assertSame('emitido',$orig->fresh()->estado);
 }
 public function test_duplicar_omite_productos_inactivos():void {
  $u=$this->user();$this->cliente();$a=$this->producto('A-1','10.00');$b=$this->producto('B-1','20.00');
  $c=Cliente::first();$s=app(PresupuestoService::class);
  $p=$s->guardar(['cliente_id'=>$c->id,'vigencia'=>now()->addDays(5)->toDateString(),'items'=>json_encode([['producto_id'=>$a->id,'cantidad'=>1],['producto_id'=>$b->id,'cantidad'=>1]])],$u->id);
  $s->emitir($p,$u->id);$b->update(['activo'=>false]);
  [$nuevo,$omitidos]=$s->duplicar($p->fresh(),$u->id);
  $this->assertSame(1,$omitidos);$this->assertSame('10.00',$nuevo->fresh()->total);
 }
 public function test_documento_de_cliente_se_valida_y_normaliza():void {
  $u=$this->user();
  $this->actingAs($u)->post(route('clientes.store'),['nombre'=>'Cliente Uno','documento'=>'123'])->assertSessionHasErrors('documento');
  $this->actingAs($u)->post(route('clientes.store'),['nombre'=>'Cliente Dos','documento'=>'12345678'])->assertSessionHasNoErrors();
  $this->actingAs($u)->post(route('clientes.store'),['nombre'=>'Empresa SAC','documento'=>'20123456789'])->assertSessionHasNoErrors();
  $this->actingAs($u)->post(route('clientes.store'),['nombre'=>'Otro','documento'=>'30123456789'])->assertSessionHasErrors('documento');
  $this->assertDatabaseHas('clientes',['documento'=>'12345678','nombre'=>'Cliente Dos']);
 }
 public function test_un_servicio_no_guarda_stock():void {
  $this->actingAs($this->user())->post(route('productos.store'),[
   'codigo'=>'SRV-001','descripcion'=>'Mantenimiento preventivo','precio'=>'60','costo'=>'0','categoria'=>'Servicios',
   'stock'=>'7','stock_minimo'=>'0','es_servicio'=>'1','icono'=>'🛠','activo'=>'1',
  ])->assertSessionHasNoErrors();
  $this->assertNull(Producto::where('codigo','SRV-001')->firstOrFail()->stock);
 }
 public function test_un_producto_fisico_exige_stock():void {
  $this->actingAs($this->user())->post(route('productos.store'),[
   'codigo'=>'FIS-001','descripcion'=>'Mouse','precio'=>'20','costo'=>'10','categoria'=>'Periféricos',
   'stock_minimo'=>'2','es_servicio'=>'0','icono'=>'🖱','activo'=>'1',
  ])->assertSessionHasErrors('stock');
 }
 public function test_codigo_de_producto_inactivo_se_informa_distinto_al_inexistente():void {
  $u=$this->user();$this->producto('INACT-1','10.00',['activo'=>false]);
  $r=$this->actingAs($u)->getJson(route('productos.codigo',['codigo'=>'INACT-1']))->assertNotFound();
  $this->assertStringContainsString('inactivo',$r->json('message'));
  $r2=$this->actingAs($u)->getJson(route('productos.codigo',['codigo'=>'NO-EXISTE']))->assertNotFound();
  $this->assertStringContainsString('no existe',$r2->json('message'));
 }
 public function test_alternar_activo_solo_para_administrador():void {
  $p=$this->producto('TOG-1','10.00');
  $this->actingAs($this->user('atencion'))->patch(route('productos.activo',$p))->assertForbidden();
  $this->actingAs($this->user())->patch(route('productos.activo',$p))->assertRedirect();
  $this->assertFalse($p->fresh()->activo);
 }
 public function test_login_oculta_credenciales_de_prueba_fuera_del_modo_demo():void {
  config(['app.demo'=>true]);$this->get('/login')->assertOk()->assertSee('Demo2026!');
  config(['app.demo'=>false]);$this->get('/login')->assertOk()->assertDontSee('Demo2026!');
 }
 public function test_el_panel_carga_con_graficos_y_alertas():void {
  $this->seed();
  $u=Usuario::where('correo','admin@pccenter.test')->firstOrFail();
  $this->actingAs($u)->get(route('panel'))->assertOk()->assertSee('Importe por mes')->assertSee('Stock bajo')->assertSee('Por vencer');
 }
 public function test_cabeceras_de_seguridad_presentes():void {
  $this->get('/login')->assertHeader('X-Frame-Options','SAMEORIGIN')->assertHeader('X-Content-Type-Options','nosniff');
 }
 public function test_pagina_403_personalizada():void {
  $p=$this->producto('TOG-2','10.00');
  $this->actingAs($this->user('atencion'))->patch(route('productos.activo',$p))->assertForbidden()->assertSee('Acceso denegado');
 }
}