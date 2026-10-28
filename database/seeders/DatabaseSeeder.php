<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{Usuario,Cliente,Producto};
class DatabaseSeeder extends Seeder
{
 public function run(): void {
  Usuario::firstOrCreate(['correo'=>'admin@pccenter.test'],['nombre'=>'Administrador Demo','clave_hash'=>Hash::make('Demo2026!'),'rol'=>'administrador','activo'=>true]);
  Usuario::firstOrCreate(['correo'=>'atencion@pccenter.test'],['nombre'=>'Asesor Comercial Demo','clave_hash'=>Hash::make('Demo2026!'),'rol'=>'atencion','activo'=>true]);
  foreach ([
    ['documento'=>'20100000001','nombre'=>'Empresa Ejemplo S.A.C.','correo'=>'contacto@ejemplo.test','telefono'=>'900000001'],
    ['documento'=>'00000001','nombre'=>'Cliente Demostración','correo'=>'cliente@ejemplo.test','telefono'=>'900000002'],
    ['documento'=>'20100000002','nombre'=>'Oficina Modelo E.I.R.L.','correo'=>'compras@ejemplo.test','telefono'=>'900000003'],
  ] as $x) Cliente::firstOrCreate(['documento'=>$x['documento']],$x);
  foreach ([
   ['codigo'=>'000123450001','descripcion'=>'SSD Kingston NV3 500 GB NVMe','precio'=>'169.90'],
   ['codigo'=>'000123450002','descripcion'=>'Memoria RAM DDR4 8 GB 3200 MHz','precio'=>'89.00'],
   ['codigo'=>'000123450003','descripcion'=>'Mouse inalámbrico ergonómico','precio'=>'45.50'],
   ['codigo'=>'000123450004','descripcion'=>'Teclado USB español','precio'=>'39.90'],
   ['codigo'=>'000123450005','descripcion'=>'Monitor IPS 24 pulgadas FHD','precio'=>'499.00'],
   ['codigo'=>'000123450006','descripcion'=>'Router Wi-Fi doble banda AC1200','precio'=>'139.00'],
   ['codigo'=>'000123450007','descripcion'=>'Cable de red Cat 6 (3 metros)','precio'=>'17.50'],
   ['codigo'=>'000123450008','descripcion'=>'Unidad USB 64 GB','precio'=>'32.00'],
   ['codigo'=>'000123450009','descripcion'=>'Disco externo 1 TB USB 3.0','precio'=>'249.90'],
   ['codigo'=>'000123450010','descripcion'=>'Webcam Full HD con micrófono','precio'=>'115.00'],
   ['codigo'=>'000123450011','descripcion'=>'Adaptador HDMI a VGA','precio'=>'28.00'],
   ['codigo'=>'000123450012','descripcion'=>'Fuente de poder 550 W','precio'=>'185.00'],
  ] as $x) Producto::firstOrCreate(['codigo'=>$x['codigo']], $x+['activo'=>true]);
  $this->call([CatalogoDemoSeeder::class, PresupuestosDemoSeeder::class]);
 }
}
