<!doctype html>
<html lang="es"><head><meta charset="UTF-8">
<style>
@page{margin:28px 37px 46px}
body{font-family:DejaVu Sans,Arial,sans-serif;font-size:10px;color:#1d3149;margin:0}
*{box-sizing:border-box}
.header{width:100%;border-bottom:4px solid #096ab0;padding-bottom:15px;margin-bottom:20px}
.header td{vertical-align:middle}
.logo{width:57px;height:99px;object-fit:contain}
.brandname{color:#0b3a68;font-size:24px;font-weight:bold;letter-spacing:.7px;margin:0}
.subtitle{font-size:9px;color:#5b7185;margin-top:5px}
.smalltag{font-size:9px;color:#067cb6;font-weight:bold;letter-spacing:1.5px;margin:0 0 7px}
.docnum{font-size:22px;font-weight:bold;color:#132d4f;margin:0}
.boxhead{width:100%;margin:0 0 22px;border-collapse:collapse}
.boxhead td{width:50%;border:1px solid #e0e9ef;padding:12px 14px;vertical-align:top;background:#fbfdff}
.boxhead td:first-child{border-right:0}
.label{font-size:8px;letter-spacing:1.1px;font-weight:bold;color:#70869a;text-transform:uppercase;margin-bottom:8px}
.value{font-size:11px;color:#1b344f;font-weight:bold;margin-bottom:5px}
.smallline{line-height:1.65;color:#62768b;font-size:9px}
.lineitems{width:100%;border-collapse:collapse;font-size:9px}
.lineitems thead th{text-align:left;background:#123b65;color:#fff;padding:10px 9px;font-size:8px;letter-spacing:.3px}
.lineitems td{border-bottom:1px solid #e5ecf3;padding:10px 9px;vertical-align:top}
.lineitems tbody tr:nth-child(even){background:#f7fbfe}
.lineitems th.right,.lineitems td.right{text-align:right}
.lineitems td.center,.lineitems th.center{text-align:center}
.lineitems tr{page-break-inside:avoid}
.productname{font-weight:bold;color:#1d344e}
.productcode{display:block;color:#90a1af;font-size:8px;margin-top:3px}
.totals{width:100%;margin-top:21px;border-collapse:collapse}
.totals td{padding:10px 13px;background:#eaf5fc;color:#15507e}
.totals td:last-child{text-align:right;font-size:21px;font-weight:bold;color:#006da9}
.totals td:first-child{font-size:11px;font-weight:bold}
.terms{margin-top:26px;background:#f6f9fc;border-left:3px solid #08a3d9;padding:13px 14px;line-height:1.65;color:#556b80;font-size:9px}
.terms strong{color:#1b3c5d}
.footer{position:fixed;bottom:-27px;left:0;right:0;border-top:1px solid #dce7ef;padding-top:9px;color:#8291a0;font-size:8px}
.footer td{vertical-align:top}
.lineitems thead{display:table-header-group}
.estado-aviso{display:block;margin-top:8px;font-size:10px;font-weight:bold;color:#b4233a;border:1px solid #e9a7b1;background:#fff1f3;padding:4px 7px;text-align:center}
.estado-aviso.ok{color:#11705a;border-color:#a9dccb;background:#effaf5}
.pagina:after{content:"Página " counter(page)}
.demo{display:block;font-size:9px;color:#a86b1c;font-weight:bold;margin-top:8px}
</style></head><body>
@php
 $logoPath=public_path('img/logo-pccenter-tesina.png');
 $logoData=is_file($logoPath)?'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)):null;
 $esDemo=str_starts_with((string)$presupuesto->numero,'DEMO-');
@endphp
<table class="header"><tr>
<td style="width:75px">@if($logoData)<img class="logo" src="{{ $logoData }}" alt="PC Center">@endif</td>
<td><div class="brandname">PC CENTER</div><div class="subtitle">Soluciones integrales en informática</div><div class="subtitle">Productos y servicios tecnológicos</div></td>
<td style="width:240px;text-align:right"><p class="smalltag">PROPUESTA COMERCIAL</p><p class="docnum">PRESUPUESTO</p><p style="color:#446885;font-weight:bold;font-size:11px;margin-top:7px">{{ $presupuesto->numero }}</p>@if($esDemo)<span class="demo">DOCUMENTO DE DEMOSTRACIÓN</span>@endif
@if($presupuesto->estado==='anulado')<span class="estado-aviso">DOCUMENTO ANULADO · SIN VALIDEZ</span>@elseif($presupuesto->estado==='vencido'||($presupuesto->estado==='emitido'&&$presupuesto->vigenciaSuperada()))<span class="estado-aviso">OFERTA VENCIDA</span>@elseif($presupuesto->estado==='rechazado')<span class="estado-aviso">PRESUPUESTO RECHAZADO</span>@elseif($presupuesto->estado==='aceptado')<span class="estado-aviso ok">PRESUPUESTO ACEPTADO</span>@endif</td>
</tr></table>
<table class="boxhead"><tr>
<td><div class="label">Datos del cliente</div><div class="value">{{ $presupuesto->cliente->nombre }}</div><div class="smallline">
@if($presupuesto->cliente->documento)Documento: {{ $presupuesto->cliente->documento }}<br>@endif
@if($presupuesto->cliente->correo)Correo: {{ $presupuesto->cliente->correo }}<br>@endif
@if($presupuesto->cliente->telefono)Teléfono: {{ $presupuesto->cliente->telefono }}@endif
</div></td>
<td><div class="label">Información del presupuesto</div><div class="smallline"><strong>Emisión:</strong> {{ $presupuesto->fecha->format('d/m/Y') }}<br><strong>Vigencia:</strong> {{ $presupuesto->vigencia->format('d/m/Y') }}<br><strong>Atendido por:</strong> {{ $presupuesto->usuario->nombre }}<br><strong>N.º de documento:</strong> {{ $presupuesto->numero }}<br><strong>Moneda:</strong> Soles (PEN)</div></td>
</tr></table>
<table class="lineitems"><thead><tr><th style="width:44%">DESCRIPCIÓN DEL PRODUCTO</th><th class="center" style="width:11%">CANT.</th><th class="right" style="width:22%">PRECIO UNIT.</th><th class="right" style="width:23%">IMPORTE</th></tr></thead><tbody>
@foreach($presupuesto->detalles as $d)<tr><td><span class="productname">{{ $d->descripcion_snapshot }}</span>@if($d->producto)<span class="productcode">Código: {{ $d->producto->codigo }}</span>@endif</td><td class="center">{{ $d->cantidad }}</td><td class="right">S/ {{ number_format($d->precio_snapshot,2) }}</td><td class="right">S/ {{ number_format($d->subtotal,2) }}</td></tr>@endforeach
</tbody></table>
<table class="totals"><tr><td>TOTAL PRESUPUESTADO</td><td>S/ {{ number_format($presupuesto->total,2) }}</td></tr></table>
<div class="terms"><strong>Condiciones de la propuesta</strong><br>
Los precios corresponden a valores finales registrados en el catálogo al guardar el documento. La disponibilidad de los productos debe confirmarse antes de cualquier venta. La propuesta tiene vigencia hasta la fecha indicada. Este documento es informativo y <strong>no constituye factura, boleta de venta ni comprobante de pago</strong>.</div>
<table class="footer" width="100%"><tr><td>PC CENTER · Presupuestos comerciales · Prototipo académico 2026</td><td align="right">Documento generado electrónicamente · <span class="pagina"></span></td></tr></table>
</body></html>
