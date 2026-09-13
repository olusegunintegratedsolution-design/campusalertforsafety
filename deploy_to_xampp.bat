@echo off
title Deploy Federal Polytechnic Ilaro Campus Safety to XAMPP
echo ======================================================================
echo   Federal Polytechnic Ilaro - Campus Safety & Emergency Alert System
echo   Deploying to C:\xampp\htdocs\campus-safety ...
echo ======================================================================
echo.

set TARGET=C:\xampp\htdocs\campus-safety

if not exist "%TARGET%" (
    echo [*] Creating target directory: %TARGET%
    mkdir "%TARGET%"
)

echo [*] Copying application files to %TARGET% ...
xcopy "%~dp0*" "%TARGET%\" /E /Y /I /Q

echo.
echo [*] Initializing database via setup_db.php ...
if exist "C:\xampp\php\php.exe" (
    "C:\xampp\php\php.exe" "%TARGET%\setup_db.php"
) else (
    echo [!] PHP not found at C:\xampp\php\php.exe. Please run setup_db.php in browser.
)

echo.
echo ======================================================================
echo   DEPLOYMENT COMPLETE!
echo   Open in browser: http://localhost/campus-safety/
echo ======================================================================
echo.
pause
