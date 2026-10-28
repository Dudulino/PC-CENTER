@echo off
chcp 65001 >nul
title PC Center - Instalación local
cd /d "%~dp0"
echo ===========================================
echo    PC CENTER - PROTOTIPO LARAVEL 12
echo ===========================================
echo.
where php >nul 2>&1
if errorlevel 1 (echo ERROR: Instala PHP 8.2 o superior y agregalo al PATH.& pause & exit /b 1)
where composer >nul 2>&1
if errorlevel 1 (echo ERROR: Instala Composer y agregalo al PATH.& pause & exit /b 1)
if not exist .env copy .env.example .env >nul
echo Verifica en el archivo .env los datos MySQL antes de continuar.
echo La base de datos pc_center debe estar creada en phpMyAdmin.
set /p CONTINUAR="Presiona ENTER para instalar dependencias..."
call composer install
if errorlevel 1 (echo ERROR al instalar Composer.& pause & exit /b 1)
php artisan key:generate
if errorlevel 1 (echo ERROR al generar APP_KEY.& pause & exit /b 1)
php artisan migrate --seed
if errorlevel 1 (echo ERROR en MySQL o migraciones. Verifica DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD en .env.& pause & exit /b 1)
echo.
echo Instalación completada. Acceso: http://127.0.0.1:8000
start "" http://127.0.0.1:8000
echo Presiona CTRL+C para cerrar el servidor.
php artisan serve --host=127.0.0.1 --port=8000
pause
