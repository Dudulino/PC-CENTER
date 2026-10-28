# PC Center · Mejoras de la versión 3.1

Se mantuvo la estructura, la base de datos y el diseño del proyecto: **no hay migraciones nuevas**. Para actualizar sobre un proyecto ya instalado basta con ejecutar `ACTUALIZAR_PC_CENTER.bat` (o `php artisan optimize:clear`).

## Errores corregidos
| Problema | Dónde | Solución |
|---|---|---|
| Texto crudo `@elseif($p->stock...)` en las tarjetas del formulario de presupuesto | `presupuestos/form.blade.php` | Blade no procesa una directiva pegada a una palabra (`disponible@elseif`). La disponibilidad se calcula ahora en `Producto::disponibilidad_texto` y se reutiliza en catálogo y formulario. |
| Editar un usuario daba error 405 | `usuarios/index.blade.php` | `@csrf@method('PUT')` pegados: el método PUT no se enviaba. |
| Errores de validación mostrados como `validation.required` | falta de `lang/es` | Se añadieron `lang/es/validation.php`, `auth.php` y `pagination.php` con textos y nombres de campo en español. |

## Lógica de negocio (alineada con la tesina, cap. IV 4.2)
- **Vencimiento por vigencia:** un presupuesto emitido pasa solo a *vencido* cuando termina su vigencia (al abrir panel, historial o documento, y con `php artisan presupuestos:vencer`). Queda registrado en el historial como «Sistema».
- **Transiciones válidas:** *aceptado* solo si la oferta está vigente; *vencido* solo si ya terminó; *rechazado* y *anulado* exigen motivo (mín. 5 caracteres). Un estado final no vuelve a cambiar.
- **Duplicar como borrador:** «un cambio comercial requerirá un nuevo presupuesto» → botón que copia cliente y cantidades con los precios vigentes del catálogo (omite productos inactivos y lo informa).
- **Productos:** un servicio nunca guarda stock; un producto físico exige stock; precio mínimo S/ 0.01; activar/desactivar con un clic (sin borrar, para conservar el histórico).
- **Clientes:** documento validado y normalizado (DNI 8 dígitos, RUC 11 dígitos con prefijo 10/15/16/17/20, CE/pasaporte 9–12 caracteres), teléfono y nombre limpios, sin duplicados.
- **Escaneo:** el mensaje distingue «el código no existe» de «el producto está inactivo» (RF04).
- **Stock referencial:** aviso (no bloqueante) cuando la cantidad supera el stock; el sistema sigue sin descontar existencias, como indica la tesina.
- **Contraseñas:** mínimo 8 caracteres con letras y números.

## Seguridad y trazabilidad (RNF01, RF11)
- Bitácora de eventos en `storage/logs/auditoria-AAAA-MM-DD.log`: inicio/cierre de sesión, intentos fallidos (solo correo, nunca contraseña), emisión, cambios de estado, altas/cambios de productos (con precio anterior → nuevo), usuarios y respaldos.
- Cabeceras de seguridad (`X-Frame-Options`, `nosniff`, `Referrer-Policy`, `Permissions-Policy`).
- Las credenciales de prueba del login solo se muestran con `APP_DEMO=true` (en `.env`). **Ponlo en `false` fuera de la exposición.**

## Presentación y usabilidad
- Panel: gráfico de importe por mes (sin librerías externas), presupuestos por vencer, productos con stock bajo y aviso de borradores pendientes.
- Formulario de presupuesto: atajos **F2** (ir al lector) y **Esc** (limpiar), botón «Vaciar detalle», aviso al salir con cambios sin guardar y bloqueo de doble envío.
- Login con botón mostrar/ocultar contraseña. Insignia de borradores en el menú. Nombre del usuario en la barra superior.
- PDF: número de página, encabezado de tabla repetido, rótulo visible para documentos **anulados, vencidos, rechazados o aceptados**.
- Páginas de error 403, 404, 419, 429, 500 y 503 propias (con `APP_DEBUG=false`, o siempre para errores HTTP como 403/404).

## Pruebas
`tests/Feature/MejorasTest.php` agrega 20 pruebas (vencimiento, transiciones, duplicado, validaciones, permisos, cabeceras de seguridad, panel). Ejecuta `php artisan test`.

## Pendiente a tu criterio (no se tocó para no salir del alcance de la tesina)
- IGV desglosado: la tesina define precios finales sin impuestos añadidos.
- Descuento automático de stock, facturación y cobros: fuera de alcance según la tesina.
