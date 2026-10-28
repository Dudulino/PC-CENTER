<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Producto extends Model
{
    protected $table = 'productos';
    protected $fillable = ['codigo', 'descripcion', 'precio', 'activo', 'categoria', 'sku', 'costo', 'stock', 'stock_minimo', 'es_servicio', 'icono'];
    protected $casts = ['activo'=>'boolean','es_servicio'=>'boolean','precio'=>'decimal:2','costo'=>'decimal:2','stock'=>'integer','stock_minimo'=>'integer'];
    public function getImagenCatalogoAttribute(): string {
        $n=mb_strtolower($this->descripcion ?? '');
        if(str_contains($n,'mouse')) return 'mouse.svg';
        if(str_contains($n,'teclado')) return 'keyboard.svg';
        if(str_contains($n,'monitor')) return 'monitor.svg';
        if(str_contains($n,'ram') || str_contains($n,'memoria')) return 'ram.svg';
        if(str_contains($n,'webcam') || str_contains($n,'cámara')) return 'camera.svg';
        if(str_contains($n,'auriculares')) return 'headset.svg';
        if(str_contains($n,'router') || str_contains($n,'switch') || str_contains($n,'access point')) return 'router.svg';
        if(str_contains($n,'impresora') || str_contains($n,'tinta')) return 'printer.svg';
        if(str_contains($n,'laptop') || str_contains($n,'mini pc')) return 'laptop.svg';
        if(str_contains($n,'cable') || str_contains($n,'hub') || str_contains($n,'adaptador')) return 'cable.svg';
        if(str_contains($n,'fuente') || str_contains($n,'ups')) return 'power.svg';
        if(str_contains($n,'servicio') || str_contains($n,'instalación') || str_contains($n,'mantenimiento') || str_contains($n,'limpieza')) return 'tools.svg';
        if(str_contains($n,'disco') || str_contains($n,'ssd') || str_contains($n,'unidad')) return 'ssd.svg';
        if(str_contains($n,'procesador') || str_contains($n,'placa') || str_contains($n,'tarjeta')) return 'cpu.svg';
        return match($this->categoria){
            'Almacenamiento'=>'ssd.svg', 'Periféricos'=>'keyboard.svg',
            'Monitores'=>'monitor.svg', 'Redes'=>'router.svg',
            'Equipos'=>'laptop.svg', 'Impresoras'=>'printer.svg',
            'Servicios'=>'tools.svg', 'Accesorios'=>'cable.svg', default=>'cpu.svg'
        };
    }
    /** Foto de una categoría similar, no necesariamente del modelo vendido. */
    public function getImagenRemotaAttribute(): ?string {
        // Por defecto se usan los iconos locales (public/img/catalogo). Fotos remotas: IMAGENES_REMOTAS=true en .env
        if (!config('imagenes_catalogo.activar', false)) return null;
        $fotos = config('imagenes_catalogo.fotos', []);
        $sufijo = substr((string) ($this->codigo ?? ''), -3);
        $clave = config('imagenes_catalogo.productos_demo.'.$sufijo);
        // Aplicar el mapa de demostración solo a los códigos precargados.
        if (!str_starts_with((string) ($this->codigo ?? ''), '000123450')) $clave = null;
        if (!$clave) {
            $d = mb_strtolower((string) ($this->descripcion ?? ''), 'UTF-8');
            $reglas = [
                'ssd' => 'nvme', 'disco' => 'ssd', 'usb' => 'usb',
                'memoria' => 'ram4', 'ram' => 'ram4', 'mouse' => 'mouse',
                'teclado' => 'keyboard', 'monitor' => 'monitor',
                'router' => 'router', 'switch' => 'router', 'access point' => 'router',
                'cable' => 'cable', 'adaptador' => 'cable', 'hub' => 'cable',
                'webcam' => 'webcam', 'fuente' => 'power', 'ups' => 'power',
                'auricular' => 'headset', 'impresora' => 'printer',
                'tinta' => 'printer', 'laptop' => 'laptop', 'procesador' => 'cpu',
                'tarjeta gráfica' => 'gpu', 'placa madre' => 'board',
                'gabinete' => 'case', 'disipador' => 'fan', 'mini pc' => 'case',
                'instalación' => 'service', 'mantenimiento' => 'service', 'limpieza' => 'service',
            ];
            foreach ($reglas as $fragmento => $categoria) {
                if (str_contains($d, $fragmento)) { $clave=$categoria; break; }
            }
            if (!$clave) $clave = match($this->categoria) {
                'Almacenamiento' => 'ssd', 'Componentes' => 'board',
                'Periféricos' => 'mouse', 'Monitores' => 'monitor',
                'Redes' => 'router', 'Equipos' => 'laptop',
                'Impresoras' => 'printer', 'Servicios' => 'service',
                'Accesorios' => 'cable', default => null,
            };
        }
        $nombre = ($clave && isset($fotos[$clave])) ? $fotos[$clave]['archivo'] : null;
        if (!$nombre) return null;
        // Redirección oficial de Commons a miniaturas; no requiere claves ni cuenta API.
        return 'https://commons.wikimedia.org/wiki/Special:FilePath/'
            . rawurlencode('File:'.$nombre) . '?width=500';
    }
    /** Texto de disponibilidad referencial (se usa en catálogo y en el formulario de presupuestos). */
    public function getDisponibilidadTextoAttribute(): string {
        return match($this->inventario_estado) {
            'servicio' => 'Servicio disponible',
            'sin-dato' => 'Stock por confirmar',
            'agotado' => 'Sin stock referencial',
            'bajo' => 'Stock bajo · '.$this->stock,
            default => $this->stock.' unidades ref.',
        };
    }
    public function getInventarioEstadoAttribute(): string {
        if ($this->es_servicio) return 'servicio';
        if ($this->stock === null) return 'sin-dato';
        if ($this->stock <= 0) return 'agotado';
        if ($this->stock <= $this->stock_minimo) return 'bajo';
        return 'disponible';
    }
}
