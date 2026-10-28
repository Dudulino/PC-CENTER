@echo off
chcp 65001 >nul
setlocal
title PC Center - Actualizar sin borrar datos
cd /d "%~dp0"
echo ======================================================
echo  PC CENTER - ACTUALIZACION DEL CATALOGO Y PRESUPUESTOS
echo ======================================================
echo.
if not exist .env (
  echo ERROR: Archivo .env no encontrado. Copia la carpeta nueva sobre tu proyecto ya configurado.
  pause
  exit /b 1
)
if not exist vendor\autoload.php (
  echo ERROR: No se encontro la carpeta vendor. Instala dependencias con Composer.
  pause
  exit /b 1
)
where php >nul 2>&1
if errorlevel 1 (
  echo ERROR: No se encuentra PHP en el PATH.
  pause
  exit /b 1
)
echo [1/3] Limpiando cache de la aplicacion...
php artisan optimize:clear
if errorlevel 1 goto fallo
echo [2/3] Aplicando solo migraciones pendientes (NO se borran tablas)...
php artisan migrate --force
if errorlevel 1 goto fallo
echo [3/3] Cargando productos, clientes y presupuestos ficticios sin duplicar...
php artisan db:seed --force
if errorlevel 1 goto fallo
php artisan view:clear
if errorlevel 1 goto fallo
echo.
echo ACTUALIZACION COMPLETADA. No se eliminaron los datos existentes.
echo Recarga la pagina del sistema con CTRL+F5.
pause
exit /b 0
:fallo
echo.
echo ERROR: Revisa la salida anterior y confirma que MySQL esta iniciado en XAMPP.
echo NO ejecutes migrate:fresh ni db:wipe.
pause
exit /b 1
