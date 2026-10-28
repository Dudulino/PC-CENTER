@extends('layouts.app')
@section('title','Créditos de imágenes')
@section('breadcrumb','Recursos / Créditos de imágenes')
@section('content')
<div class="page-head"><div><span class="eyebrow ink">RECURSOS VISUALES</span><h1>Imágenes, autores y licencias</h1><p>Fotografías ilustrativas de componentes y servicios informáticos, usadas en el catálogo de demostración.</p></div><a class="btn secondary" href="{{ route('productos.index') }}">← Catálogo</a></div>
<section class="panel padded"><p>Las fotos proceden de Wikimedia Commons y se visualizan por Internet mediante su servicio público de miniaturas, sin clave API. Los productos y sus precios son ficticios. <strong>Una foto representa el tipo de artículo, no su marca o modelo exacto.</strong> Si no se puede cargar una foto, aparece el dibujo incluido en el proyecto.</p><p>Se muestran nombre original, autor, fuente y licencia. No se afirma ningún respaldo comercial de los autores.</p><div class="photo-credits-grid">
@foreach(config('imagenes_catalogo.fotos',[]) as $clave=>$item)
 <article class="photo-credit"><h3>{{ ucfirst(str_replace('-',' ',$clave)) }}</h3><p><strong>Archivo:</strong> {{ $item['archivo'] }}</p><p><strong>Autor:</strong> {{ $item['autor'] }}</p><p><strong>Licencia:</strong> <a href="{{ $item['url_licencia'] }}" target="_blank" rel="noopener noreferrer">{{ $item['licencia'] }} ↗</a></p><a href="https://commons.wikimedia.org/wiki/File:{{ rawurlencode(str_replace(' ','_',$item['archivo'])) }}" target="_blank" rel="noopener noreferrer">Ver fotografía y ficha original ↗</a></article>
@endforeach
</div><p class="muted">Condiciones de licencias: <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank" rel="noopener noreferrer">CC BY</a>, <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank" rel="noopener noreferrer">CC BY-SA</a> y <a href="https://creativecommons.org/publicdomain/zero/1.0/" target="_blank" rel="noopener noreferrer">CC0</a>. La licencia concreta de cada archivo es la indicada en su ficha.</p></section>
@endsection
