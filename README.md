# PC Center — Sistema web de generación de presupuestos

**Prototipo académico basado en el capítulo IV de la tesina de PC Center (2026).**

Sistema web con **PHP 8.2+, Laravel 12, MySQL 8**, Blade, HTML, CSS, JavaScript y generación de **PDF con Dompdf**. Incluye escaneo de códigos por lector USB en modo teclado, búsqueda manual de productos y cámara opcional en navegadores compatibles.

> **Importante:** Todos los usuarios, clientes y productos precargados son ficticios. El software de esta carpeta no demuestra una instalación o resultados obtenidos previamente en PC Center. Sirve para hacer una demostración real, ejecutar pruebas y generar capturas de la aplicación levantada localmente. Necesita PHP, Composer y MySQL para arrancar. No se incluyen las dependencias de Composer (`vendor/`): se instalan con `composer install`.

## 1. Abrir en Visual Studio Code (Windows)

**Requisitos**: Windows con PHP >= 8.2 y extensiones `mbstring`, `pdo_mysql`, `openssl`, `xml`, `fileinfo`, `zip`, `gd` (por Dompdf). Puedes utilizar Laragon o XAMPP para PHP/MySQL. Instala Composer y, opcionalmente, Visual Studio Code con su extensión PHP Intelephense y Laravel.

1. Extrae el ZIP **completo**. Abre la carpeta `PC_Center_Sistema_Web_Laravel` en Visual Studio Code usando **Archivo > Abrir carpeta**.
2. Inicia MySQL desde Laragon/XAMPP. En phpMyAdmin crea la base de datos `pc_center` (puedes ejecutar `database/crear_bd_pc_center.sql`).
3. En **Terminal > Nuevo terminal**, escribe:

```powershell
Copy-Item .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

4. Abre **http://127.0.0.1:8000** en tu navegador.

**Alternativa:** Ejecuta `INSTALAR_EN_WINDOWS.bat` después de crear la base de datos y revisar el `.env`. Cuando termines, `INICIAR_SISTEMA.bat` vuelve a levantar la aplicación sin reinstalarla.

### Si usas contraseña en MySQL

Edita `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pc_center
DB_USERNAME=root
DB_PASSWORD=TU_CLAVE_REAL
```

Si tu PHP no se reconoce en la terminal, instala PHP / Laragon y configura `PATH` para que funcione `php -v`. Comprueba Composer con `composer -V`.

## 2. Cuentas de demostración

| Rol | Correo | Contraseña |
|---|---|---|
| Administración | `admin@pccenter.test` | `Demo2026!` |
| Atención comercial | `atencion@pccenter.test` | `Demo2026!` |

Las cuentas solo se crean al ejecutar `php artisan migrate --seed` en una base de datos nueva. **No uses estas credenciales en una instalación pública**. Después de la exposición cámbialas y pon `APP_DEMO=false` en `.env` para ocultarlas del login.

## 3. Funcionalidades incluidas

| Caso / requisito | Ubicación | Implementación |
|---|---|---|
| CU01 / RF01: autenticación y roles | `/login`, `/usuarios` | Sesiones Laravel, contraseñas hash, middleware por rol. |
| CU02 / RF02: productos | `/productos` | CRUD de catálogo para administrador, códigos únicos y activación/desactivación. |
| CU03 / RF03: clientes | `/clientes` | Registro, edición, búsqueda y cliente rápido desde el formulario. |
| CU04 / RF04–RF05: escaneo / búsqueda | `/presupuestos/nuevo` | Lector USB como teclado + Enter, búsqueda alternativa y cámara opcional. |
| CU05 / RF06–RF08: cálculo y guardado | `/presupuestos/nuevo` | Cantidades validadas, cálculos y transacciones en servidor, borradores editables. |
| CU06 / RF09–RF11: emisión y consulta | `/presupuestos/{id}` | Emisión idempotente con número único, PDF, historial de estados, trazabilidad. |
| RF12: respaldo y restauración | `/respaldo` | Administrador descarga/restaura JSON de demostración. |

Reglas implementadas: precios finales en **PEN sin impuestos añadidos por separado**; total recalculado en backend; se guardan precios y descripciones de cada detalle; solo se edita un borrador; estados `borrador`, `emitido`, `aceptado`, `rechazado`, `vencido` y `anulado`; la atención comercial no modifica el catálogo ni usuarios.

**Fuera de alcance**, como en la tesina: facturación electrónica, cobros, inventario, pasarela de pago y despliegue en producción.

## 4. Cómo demostrarlo durante la exposición

1. Entra con `admin@pccenter.test` y contraseña `Demo2026!`.
2. Abre **Productos**: enseña un artículo con un código como `000123450001` (observa los ceros iniciales). Si quieres, crea uno nuevo.
3. Abre **Clientes**: muestra los registros y registra un cliente ficticio.
4. En **Nuevo presupuesto**, selecciona el cliente, escribe `000123450001` en el campo de escaneo y presiona **Enter**. Verás el producto agregado.
5. Escanea otra vez el mismo código para verificar que aumenta la cantidad, y agrega un producto con la búsqueda manual.
6. Guarda el borrador; comprueba el total y usa **Emitir presupuesto**. Descarga el PDF generado.
7. Abre **Presupuestos**: filtra por cliente, número o fecha. Vuelve al detalle para ver el historial. Como administrador, cambia el estado a `aceptado`.
8. Sal e inicia sesión como `atencion@pccenter.test`: intenta entrar a `/productos` para comprobar que se puede consultar, pero no editar. Si intentas enviar una petición de modificación, el servidor la bloquea.

## 5. Capturas para reemplazar los prototipos del capítulo IV

| Figura en la tesina | Captura que debes hacer de la aplicación en ejecución |
|---|---|
| Figura 32. Prototipo de acceso | Pantalla `/login` con la interfaz de inicio de sesión. |
| Figura 33. Prototipo de catálogo de productos | Pantalla `/productos` con la tabla y, opcionalmente, la ventana de edición. |
| Figura 34. Prototipo de nuevo presupuesto | Pantalla `/presupuestos/nuevo` con artículos agregados y total visible. |
| Figura 35. Prototipo de historial de presupuestos | Pantalla `/presupuestos` después de crear y emitir documentos ficticios. |
| Figura 36. Prototipo de documento para el cliente | PDF real descargado y abierto desde el navegador. |

Las **capturas reales deben tomarse del Laravel levantado**. La carpeta `VISTA_PREVIA` contiene maquetas estáticas de ejemplo y **no** evidencia la conexión con MySQL ni la funcionalidad del backend. En el texto de la tesis puedes indicar *captura del prototipo local implementado con datos simulados*, sin atribuirlo a una implantación en el negocio.

## 6. Pruebas de aceptación recomendadas (alineadas con P01–P15)

- P01: buscar/registrar `000123450001` y verificar que los ceros iniciales no desaparecen.
- P02: escanear código inexistente o inactivo y verificar mensaje sin agregar línea.
- P03: escanear dos veces el mismo artículo y comprobar cantidad +1.
- P04: intentar cantidad 0, negativa o decimal; debe rechazarse.
- P05: dos unidades a S/ 150.00 y tres a S/ 12.50 dan total S/ 337.50. Puedes crear productos con esos precios para probarlo.
- P06: emitir presupuesto, cambiar precio del catálogo y comprobar que el PDF conserva el precio emitido.
- P07: provocar dato inválido en un segundo detalle y comprobar que no aparece un presupuesto parcial.
- P08: emitir dos veces: el número y el documento no deben duplicarse.
- P09: usuario de atención no puede ejecutar gestión administrativa de catálogo y usuarios.
- P10: PDF con descripciones largas y varias líneas; revisar cortes y totales.
- P11: exportar respaldo y restaurarlo en un entorno **aislado**, comprobando integridad.
- P12: si la cámara falla o no se soporta, usar USB/búsqueda manual.
- P13: emitir y cambiar a aceptado; verificar historial y responsable.
- P14: filtrar por número, nombre del cliente y fecha.
- P15: comparar dos modelos similares con diferentes códigos y comprobar la descripción exacta.

Hay pruebas automatizadas de lógica y autorización en `tests/Feature`. Para ejecutarlas usa `php artisan test` con `.env.testing` y extensión `pdo_sqlite`. No reemplazan la validación manual ni la revisión del profesor.

## 7. Organización del proyecto

```text
app/
  Http/Controllers/         Login, catálogo, clientes, presupuestos, usuarios, respaldos
  Http/Middleware/          Control de rol
  Models/                   Usuarios, clientes, productos, presupuestos, detalles, estados
  Services/                 Cálculo y emisión de presupuestos
bootstrap/                  Arranque de Laravel 12
config/                     Configuración de aplicación y acceso
routes/web.php              Rutas protegidas
resources/views/            Páginas Blade y plantilla PDF
database/migrations/        Tablas con claves foráneas
database/seeders/           Datos ficticios iniciales
public/css/ public/js/      Interfaz y lector USB / cámara
VISTA_PREVIA/              Maquetas HTML (sin base de datos)
tests/Feature/              Pruebas automatizadas
```

## 8. Limitaciones y seguridad

- La instalación depende de Composer y MySQL, y requiere PHP con extensiones activas. El ZIP contiene **código fuente**, no la carpeta `vendor`. No es un archivo HTML listo para usar como backend.
- La cámara utiliza ZXing Browser 0.2.1 como lector de códigos EAN/Code 128/QR. Se descarga desde UNPKG la primera vez que se pulsa el botón, por lo que requiere conexión a Internet además de permiso de cámara, `localhost` o HTTPS. La lectura se realiza en el navegador. Los lectores USB tipo teclado funcionan sin la cámara al enfocar el campo de código.
- Respaldos JSON: uso académico/local únicamente. Incluyen hashes de contraseñas y deben mantenerse en privado. La restauración reemplaza toda la base de datos de demostración.
- Para uso comercial real hay que completar revisión de seguridad, gestión de errores, políticas de respaldo/recuperación, pruebas de concurrencia, rendimiento, fiscalidad, protección de datos y aceptación del responsable de PC Center.
- **Estado de verificación**: los archivos PHP y JavaScript se han sometido a controles de sintaxis en el entorno de generación. No se pudo ejecutar `composer install` ni probar la integración de Laravel/MySQL aquí porque el entorno no tiene acceso a Packagist y no incluye MySQL. El arranque y las pruebas funcionales se deben realizar en el equipo donde instales las dependencias.

### Referencias técnicas

- Laravel 12: https://laravel.com/framework/docs/12.x/installation
- MySQL 8.4: https://dev.mysql.com/doc/refman/8.4/en/
- Paquete PDF: https://packagist.org/packages/barryvdh/laravel-dompdf
- API cámara: https://developer.mozilla.org/en-US/docs/Web/API/BarcodeDetector

---

## ACTUALIZACIÓN 2.0: catálogo listo y presupuesto visual (28/10/2026)

**La versión 2.0 agrega una interfaz inspirada en tus tres capturas de referencia, sin convertir el proyecto en una aplicación de cobros.**

- **Productos (`/productos`)**: información de stock referencial, costos, SKU, categorías, búsqueda y filtros; panel de indicadores y selector entre **Lista / Tarjetas**.
- **Presupuestos (`/presupuestos/nuevo`)**: catálogo visual por categorías, tarjetas seleccionables, búsqueda, lector USB, cámara opcional (ZXing), carrito con cantidades modificables y total estimado. El cálculo definitivo sigue realizándose en PHP/Laravel al guardar.
- **PDF**: plantilla mejorada con el logotipo auténtico incluido en la tesina (`public/img/logo-pccenter-tesina.png`), información de cliente, detalle, vigencia, totales y condiciones. Es un **PRESUPUESTO COMERCIAL**, no una boleta/factura electrónica.
- **Datos listos para exposición**: 38 productos ficticios, seis clientes de ejemplo y tres presupuestos ficticios emitidos/aceptados, generados por seeders idempotentes. Solo se añaden los registros inexistentes. Los precios ya editados por el usuario permanecen intactos.
- **Migración nueva**: `2026_10_28_000003_extend_product_catalog_for_demo.php`, que añade campos de consulta de inventario sin modificar los detalles comerciales guardados.
- **Cámara**: incorpora el parche ZXing anterior. Requiere Chrome/Edge con permiso de cámara y acceso a unpkg.com, o lector USB si no hay acceso.

### Si YA tienes funcionando el proyecto con XAMPP

La ruta más sencilla es descargar el **ZIP de actualización**, copiar sus archivos encima de tu proyecto y ejecutar, desde la raíz:

```powershell
.\ACTUALIZAR_PC_CENTER.bat
php artisan serve
```

Este proceso conserva `.env`, `vendor/`, clientes y presupuestos que ya existan. El script solo ejecuta `optimize:clear`, `migrate --force`, `db:seed --force` y `view:clear`. **Haz un respaldo antes**, aunque no elimina datos de forma intencional. No necesitas `npm install`, ni `migrate:fresh`, ni importar SQL manualmente.

Los módulos de inventario muestran una *referencia de existencias* útil para la demostración, pero no implementan entradas/salidas ni una venta: **generar un presupuesto no descuenta stock**. Los campos costo/stock/precios agregados son simulados; no los presentes como resultados medidos de PC Center.

Para comprobar la precarga en un entorno de pruebas con SQLite: `php artisan test --filter=CatalogoVisualTest` (necesita dependencias instaladas y `pdo_sqlite`).

El directorio `VISTA_PREVIA` contiene pantallas históricas de la versión original; para obtener capturas actuales y legítimas usa la instancia Laravel/MySQL funcionando en `127.0.0.1:8000`.


## 6. Novedades de la versión 3.1
Consulta `MEJORAS_V3_1.md`: correcciones de errores, vencimiento automático por vigencia, duplicado de presupuestos, validaciones, bitácora y mejoras de presentación. Comando útil: `php artisan presupuestos:vencer`.
