<?php
namespace App\Http\Controllers;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Support\Bitacora;

class ProductoController
{
    public function index(Request $r) {
        $q = Producto::query()
            ->when($r->filled('q'), function($query) use ($r) {
                $term = trim((string) $r->input('q'));
                $query->where(fn($b) => $b->where('codigo','like','%'.$term.'%')->orWhere('descripcion','like','%'.$term.'%')->orWhere('sku','like','%'.$term.'%'));
            })
            ->when($r->filled('categoria'),fn($query)=>$query->where('categoria',$r->input('categoria')))
            ->when($r->filled('estado'), function($query) use ($r) {
                match($r->input('estado')) {
                    'activo' => $query->where('activo',true),
                    'inactivo' => $query->where('activo',false),
                    'agotado' => $query->where('es_servicio',false)->whereNotNull('stock')->where('stock',0),
                    'bajo' => $query->where('es_servicio',false)->where('stock','>',0)->whereColumn('stock','<=','stock_minimo'),
                    'disponible' => $query->where('es_servicio',false)->whereColumn('stock','>','stock_minimo'),
                    default => null,
                };
            });
        $productos = $q->orderByDesc('activo')->orderBy('categoria')->orderBy('descripcion')->paginate(12)->withQueryString();
        $categorias = Producto::distinct()->orderBy('categoria')->pluck('categoria');
        $estadisticas = [
            'total'=>Producto::count(),
            'activos'=>Producto::where('activo',true)->count(),
            'disponibles'=>Producto::where('es_servicio',false)->whereColumn('stock','>','stock_minimo')->count(),
            'bajos'=>Producto::where('es_servicio',false)->where('stock','>',0)->whereColumn('stock','<=','stock_minimo')->count(),
            'agotados'=>Producto::where('es_servicio',false)->whereNotNull('stock')->where('stock',0)->count(),
            'valor'=>Producto::where('es_servicio',false)->selectRaw('COALESCE(SUM(costo * stock),0) as importe')->first()?->importe,
            'precio_promedio'=>Producto::avg('precio'),
        ];
        $vista = in_array($r->input('vista'),['lista','tarjetas']) ? $r->input('vista') : 'lista';
        return view('productos.index',compact('productos','categorias','estadisticas','vista'));
    }
    private function reglas(?Producto $p=null): array {
        return [
            'codigo'=>['required','string','max:64',Rule::unique('productos','codigo')->ignore($p?->id)],
            'sku'=>['nullable','string','max:64',Rule::unique('productos','sku')->ignore($p?->id)],
            'descripcion'=>['required','string','max:255'],
            'precio'=>['required','numeric','min:0.01','max:9999999999.99'],
            'costo'=>['required','numeric','min:0','max:9999999999.99'],
            'categoria'=>['required','string','max:60'],
            'stock'=>['nullable','required_if:es_servicio,0','integer','min:0','max:100000000'],
            'stock_minimo'=>['required','integer','min:0','max:100000000'],
            'es_servicio'=>['required','boolean'],
            'icono'=>['required','string','max:20'],
            'activo'=>['required','boolean'],
        ];
    }
    /** Un servicio no maneja existencias; un producto físico siempre tiene un stock referencial. */
    private function datos(Request $r, ?Producto $p=null): array {
        $r->merge(['codigo'=>is_string($r->input('codigo'))?trim($r->input('codigo')):$r->input('codigo')]);
        $d=$r->validate($this->reglas($p));
        $d['stock']=$d['es_servicio'] ? null : (int)($d['stock'] ?? 0);
        return $d;
    }
    public function store(Request $r) {
        $p=Producto::create($this->datos($r));
        Bitacora::registrar('producto.creado',['producto_id'=>$p->id,'codigo'=>$p->codigo]);
        return back()->with('ok','Producto registrado correctamente.');
    }
    public function update(Request $r,Producto $producto) {
        $precioAnterior=$producto->precio;
        $producto->update($this->datos($r,$producto));
        $cambios=['producto_id'=>$producto->id,'codigo'=>$producto->codigo];
        if((string)$precioAnterior!==(string)$producto->precio)$cambios['precio']=$precioAnterior.' → '.$producto->precio;
        Bitacora::registrar('producto.actualizado',$cambios);
        return back()->with('ok','Producto actualizado.');
    }
    /** Activar o desactivar sin borrar: los presupuestos históricos conservan su referencia. */
    public function alternarActivo(Producto $producto) {
        $producto->update(['activo'=>!$producto->activo]);
        Bitacora::registrar($producto->activo?'producto.activado':'producto.desactivado',['producto_id'=>$producto->id,'codigo'=>$producto->codigo]);
        return back()->with('ok',$producto->activo?'Producto activado.':'Producto desactivado: ya no aparece al presupuestar.');
    }
    public function buscar(Request $r) {
        $term=trim((string)$r->query('term',''));
        if(mb_strlen($term)>80) return response()->json(['message'=>'Búsqueda demasiado larga.'],422);
        $datos=Producto::where('activo',true)->when($term!=='',fn($q)=>$q->where(function($w)use($term){$w->where('codigo','like','%'.$term.'%')->orWhere('descripcion','like','%'.$term.'%')->orWhere('sku','like','%'.$term.'%');}))->orderBy('descripcion')->limit(20)->get(['id','codigo','descripcion','precio','categoria','icono','stock','es_servicio']);
        return response()->json($datos);
    }
    public function codigo(Request $r) {
        $codigo=trim((string)$r->query('codigo',''));
        abort_if($codigo===''||mb_strlen($codigo)>64,422,'Código inválido');
        $p=Producto::where('codigo',$codigo)->first(['id','codigo','descripcion','precio','categoria','icono','stock','es_servicio','activo']);
        if(!$p) return response()->json(['message'=>'El código '.$codigo.' no existe en el catálogo.'],404);
        if(!$p->activo) return response()->json(['message'=>'El producto «'.$p->descripcion.'» está inactivo y no puede presupuestarse.'],404);
        return response()->json($p->makeHidden('activo'));
    }
}
