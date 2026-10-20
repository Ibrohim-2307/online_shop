@echo off
setlocal
echo ===================================================
echo  Marketplace Shared Hosting Package Generator
echo ===================================================
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0build_hosting_package.ps1"
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Packaging failed with code %ERRORLEVEL%.
    exit /b %ERRORLEVEL%
)
echo [SUCCESS] Package ready in dist/ folder.
endlocal
