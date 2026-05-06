@echo off
cd /d "%~dp0"
:loop
php artisan queue:work --tries=3 --timeout=90
goto loop
