<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Producto,Cliente};

/** Catálogo y clientes ficticios. Idempotente: nunca sobrescribe los productos existentes. */
class CatalogoDemoSeeder extends Seeder
{
 public function run(): void {
  $data=[
   // codigo, descripcion, precio, costo, stock, stock_minimo, categoria, emoji
   ['000123450001','SSD Kingston NV3 500 GB NVMe',169.90,131.90,26,5,'Almacenamiento','💾'],
   ['000123450002','Memoria RAM DDR4 8 GB 3200 MHz',89.00,62.00,32,8,'Componentes','🧠'],
   ['000123450003','Mouse inalámbrico ergonómico',45.50,28.00,42,10,'Periféricos','🖱️'],
   ['000123450004','Teclado USB español',39.90,23.50,34,8,'Periféricos','⌨️'],
   ['000123450005','Monitor IPS 24 pulgadas FHD',499.00,388.00,12,4,'Monitores','🖥️'],
   ['000123450006','Router Wi-Fi doble banda AC1200',139.00,101.00,19,6,'Redes','📡'],
   ['000123450007','Cable de red Cat 6 (3 metros)',17.50,10.50,52,15,'Redes','🔌'],
   ['000123450008','Unidad USB 64 GB',32.00,21.00,63,12,'Almacenamiento','💾'],
   ['000123450009','Disco externo 1 TB USB 3.0',249.90,187.00,8,4,'Almacenamiento','💽'],
   ['000123450010','Webcam Full HD con micrófono',115.00,83.00,14,4,'Periféricos','📷'],
   ['000123450011','Adaptador HDMI a VGA',28.00,17.00,24,6,'Accesorios','🔌'],
   ['000123450012','Fuente de poder 550 W',185.00,143.00,9,5,'Componentes','⚡'],
   ['000123450013','SSD NVMe M.2 de 1 TB',289.00,211.00,21,5,'Almacenamiento','💾'],
   ['000123450014','Memoria RAM DDR5 16 GB 5600 MHz',239.00,183.00,18,5,'Componentes','🧠'],
   ['000123450015','Procesador AMD Ryzen 5 serie 7000',899.00,752.00,4,5,'Componentes','⚙️'],
   ['000123450016','Procesador Intel Core i5 serie 14',1050.00,858.00,3,5,'Componentes','⚙️'],
   ['000123450017','Tarjeta gráfica 8 GB GDDR6',1549.00,1282.00,2,3,'Componentes','🎮'],
   ['000123450018','Placa madre micro-ATX DDR5',595.00,462.00,7,3,'Componentes','🔧'],
   ['000123450019','Gabinete ATX con ventiladores',289.00,207.00,11,4,'Componentes','🖥️'],
   ['000123450020','Disipador por aire para CPU',115.00,78.00,15,5,'Componentes','❄️'],
   ['000123450021','Monitor gaming 27 pulgadas 165 Hz',1099.00,887.00,5,3,'Monitores','🖥️'],
   ['000123450022','Monitor oficina 22 pulgadas FHD',379.00,289.00,16,5,'Monitores','🖥️'],
   ['000123450023','Mouse gaming con iluminación RGB',109.00,69.00,13,6,'Periféricos','🖱️'],
   ['000123450024','Teclado mecánico compacto RGB',189.00,125.00,10,5,'Periféricos','⌨️'],
   ['000123450025','Auriculares con micrófono USB',149.00,102.00,17,5,'Periféricos','🎧'],
   ['000123450026','Impresora multifuncional Wi-Fi',769.00,599.00,5,4,'Impresoras','🖨️'],
   ['000123450027','Tinta para impresora negra 70 ml',39.00,24.00,28,8,'Impresoras','🖨️'],
   ['000123450028','Switch de red gigabit 8 puertos',129.00,89.00,16,5,'Redes','🌐'],
   ['000123450029','Access point de techo dual band',369.00,272.00,0,3,'Redes','📶'],
   ['000123450030','Cable HDMI 2 metros',25.00,13.00,44,12,'Accesorios','🔌'],
   ['000123450031','Hub USB-C multipuerto',119.00,74.00,21,7,'Accesorios','🔌'],
   ['000123450032','Laptop oficina 15.6 pulgadas 16 GB',2599.00,2234.00,5,2,'Equipos','💻'],
   ['000123450033','Mini PC para oficina 16 GB / 512 GB',1799.00,1442.00,3,3,'Equipos','🖥️'],
   ['000123450034','UPS interactiva 1200 VA',425.00,328.00,6,3,'Accesorios','🔋'],
   ['000123450035','Mantenimiento preventivo de PC',80.00,0.00,0,0,'Servicios','🛠️'],
   ['000123450036','Instalación de software básico',60.00,0.00,0,0,'Servicios','🧰'],
   ['000123450037','Limpieza interna de laptop',110.00,0.00,0,0,'Servicios','🛠️'],
   ['000123450038','Instalación de router y conectividad',130.00,0.00,0,0,'Servicios','📡'],
  ];
  foreach($data as [$codigo,$descripcion,$precio,$costo,$stock,$minimo,$categoria,$icono]){
   $servicio=($categoria==='Servicios');
   $p=Producto::firstOrCreate(['codigo'=>$codigo],[
    'descripcion'=>$descripcion,'precio'=>$precio,'activo'=>true,'sku'=>'PC-'.$codigo,
    'categoria'=>$categoria,'costo'=>$costo,'stock'=>$stock,'stock_minimo'=>$minimo,
    'es_servicio'=>$servicio,'icono'=>$icono,
   ]);
   // Solo completa campos nuevos de las 12 filas de demostración antiguas.
   // No altera código, descripción ni precio ya editados por el usuario.
   if($p->categoria==='Otros' && $p->sku===null){
    $p->update(['categoria'=>$categoria,'sku'=>'PC-'.$codigo,'costo'=>$costo,'stock'=>$stock,'stock_minimo'=>$minimo,'es_servicio'=>$servicio,'icono'=>$icono]);
   }
  }
  foreach([
   ['documento'=>'20100000003','nombre'=>'Comercial Andina Demo S.A.C.','correo'=>'contacto@andina.test','telefono'=>'900000004'],
   ['documento'=>'20100000004','nombre'=>'Servicios TI Modelo S.A.C.','correo'=>'compras@modelo.test','telefono'=>'900000005'],
   ['documento'=>'00000002','nombre'=>'Cliente Particular Demo','correo'=>'particular@ejemplo.test','telefono'=>'900000006'],
  ] as $c) Cliente::firstOrCreate(['documento'=>$c['documento']],$c);
 }
}
