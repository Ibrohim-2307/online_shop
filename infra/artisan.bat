@echo off
setlocal
set SCRIPT_DIR=%~dp0
pushd "%SCRIPT_DIR%..\backend"
"%SCRIPT_DIR%php84\php.exe" artisan %*
popd
endlocal
