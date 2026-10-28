@echo off
chcp 65001 >nul
setlocal
cd /d "%~dp0"
title PC CENTER - Imagenes de Wikimedia Commons
echo ==================================================
echo  PC CENTER - ACTIVAR FOTOGRAFIAS EN EL CATALOGO
echo ==================================================
echo.
if not exist .env (
  echo ERROR: Debes copiar esta actualizacion sobre tu proyecto con .env existente.
  pause
  exit /b 1
)
if not exist vendor\autoload.php (
  echo ERROR: Falta vendor. Conserva tu instalacion de Composer.
  pause
  exit /b 1
)
php artisan optimize:clear
if errorlevel 1 goto error
php artisan view:clear
if errorlevel 1 goto error
echo.
echo LISTO: Abre Productos y Nuevo presupuesto. Pulsa CTRL+F5.
echo La fotos necesitan Internet. Se usan dibujos locales como respaldo.
echo No se alteraron tablas, clientes, presupuestos ni productos.
pause
exit /b 0
:error
echo No se pudo limpiar la cache de Laravel. Revisa PHP y .env.
pause
exit /b 1
