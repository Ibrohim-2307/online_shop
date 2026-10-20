@echo off
title Online Shop & Marketplace Tizimi
chcp 65001 > nul
color 0a

echo ================================================================
echo       Online Shop - Ko'p Sotuvchili B2B/B2C Marketplace
echo ================================================================
echo.

echo [1/3] MySQL 8.0 ma'lumotlar bazasi tekshirilmoqda (Port 3306)...
netstat -ano | findstr :3306 >nul
if %errorlevel% neq 0 (
    echo [!] MySQL ishga tushirilmoqda...
    start /b "" "d:\OSPanel\modules\database\MySQL-8.0-Win10\bin\mysqld.exe" --basedir="d:/OSPanel/modules/database/MySQL-8.0-Win10" --datadir="d:/OSPanel/userdata/MySQL-8.0-Win10" --port=3306 --bind-address=0.0.0.0
) else (
    echo [+] MySQL 8.0 bazasi faol.
)
timeout /t 2 /nobreak > nul

echo [2/3] Laravel Web Server & API ishga tushirilmoqda (Port 8000)...
netstat -ano | findstr :8000 >nul
if %errorlevel% neq 0 (
    start "Marketplace Web Server (Port 8000)" cmd /k "cd /d d:\OSPanel\domains\online_shop && .\infra\php.bat .\backend\artisan serve --host=0.0.0.0 --port=8000"
) else (
    echo [+] Web Server 8000 portda ishlab turibdi.
)
timeout /t 2 /nobreak > nul

echo [3/3] Cloudflare Internet Tunneli ulanmoqda (Butun dunyo uchun HTTPS)...
start "Marketplace Internet Tunneli" cmd /k "cd /d d:\OSPanel\domains\online_shop && cloudflared.exe tunnel --protocol http2 --http-host-header localhost --url http://127.0.0.1:8000"

timeout /t 3 /nobreak > nul

echo.
echo ================================================================
echo  Barcha tizimlar muvaffaqiyatli ishga tushdi!
echo.
echo  * Do'kon Vitrinasi (Xaridor):      http://localhost:8000
echo  * Sotuvchi Kabineti (Seller):       http://localhost:8000/seller
echo  * Super Admin Paneli:               http://localhost:8000/admin
echo  * Wi-Fi tarmog'idagi telefonlar:    http://192.168.1.102:8000
echo.
echo  * BUTUN INTERNET ORQALI KIRISH UCHUN:
echo    "Marketplace Internet Tunneli" oynasidagi https://...trycloudflare.com
echo    havolasini nusxalab, telefonda yoki istalgan kompyuterda ochishingiz mumkin!
echo ================================================================
echo.
echo Oynani yopishingiz mumkin. Serverlar fonda ishlashda davom etadi.
pause
