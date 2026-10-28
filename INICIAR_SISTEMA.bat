@echo off
chcp 65001 >nul
title PC Center - Servidor local
cd /d "%~dp0"
if not exist vendor\autoload.php (echo Faltan dependencias. Ejecuta INSTALAR_EN_WINDOWS.bat primero.& pause & exit /b 1)
echo PC Center en http://127.0.0.1:8000
start "" http://127.0.0.1:8000
php artisan serve --host=127.0.0.1 --port=8000
pause
