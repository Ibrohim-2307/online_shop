@echo off
title Online Shop Platformasi (Marketplace)
chcp 65001 > nul
color 0a

echo ================================================================
echo         Online Shop Marketplace Tizimi Ishga Tushmoqda
echo ================================================================
echo.

set "SCRIPT_DIR=%~dp0"

echo [1/3] MySQL 8.0 ma'lumotlar bazasi tekshirilmoqda...
netstat -ano | findstr :3306 >nul
if %errorlevel% neq 0 (
    echo [!] MySQL ishga tushirilmoqda...
    start "MySQL Server" /b "d:\OSPanel\modules\database\MySQL-8.0-Win10\bin\mysqld.exe" --basedir="D:/OSPanel/modules/database/MySQL-8.0-Win10" --datadir="D:/OSPanel/userdata/MySQL-8.0-Win10" --port=3306 --bind-address=127.0.0.1 --console
    timeout /t 3 /nobreak > nul
) else (
    echo [✓] MySQL 3306 portida ishlab turibdi.
)

echo.
echo [2/3] Laravel Web Serveri ishga tushirilmoqda (Port 8000)...
netstat -ano | findstr :8000 >nul
if %errorlevel% neq 0 (
    start "Online Shop Server (Port 8000)" cmd /k "cd /d %SCRIPT_DIR% && .\infra\php.bat backend\artisan serve --host=0.0.0.0 --port=8000"
    timeout /t 3 /nobreak > nul
) else (
    echo [✓] Laravel serveri 8000 portida ishlab turibdi.
)

echo.
echo [3/3] Cloudflare Internet Tunneli ulanmoqda...
start "Online Shop Internet Tunneli" cmd /k "cd /d d:\OSPanel\domains\market && cloudflared.exe tunnel --protocol http2 --http-host-header localhost --url http://127.0.0.1:8000"

echo.
echo ================================================================
echo  Barcha tizimlar muvaffaqiyatli ishga tushdi!
echo.
echo  * Lokal havola (Kompyuteringizda): http://127.0.0.1:8000
echo  * Butun Internet orqali kirish manzili "Online Shop Internet Tunneli" oynasida chiqadi!
echo  * GitHub Repository: https://github.com/Ibrohim-2307/online_shop
echo ================================================================
echo.
echo Oynani yopishingiz mumkin. Serverlar fonda ishlaydi.
pause
