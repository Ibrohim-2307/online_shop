@echo off
set SCRIPT_DIR=%~dp0
"%SCRIPT_DIR%php84\php.exe" "%SCRIPT_DIR%bin\composer.phar" %*
