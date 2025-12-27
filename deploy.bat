@echo off
REM Script de deployment para Windows (FTP manual)

echo === Deployment de Backend PHP para 40 Figuritas ===
echo.

REM 1. Crear archivo .env
echo Creando archivo .env...
if not exist .env (
    copy .env.example .env
    echo * .env creado. EDITA EL ARCHIVO CON TUS CREDENCIALES:
    echo   - DB_HOST
    echo   - DB_USER
    echo   - DB_PASS
    echo   - DB_NAME
) else (
    echo * .env ya existe
)

echo.
echo === Pasos siguientes ===
echo.
echo 1. Edita el archivo .env con tus credenciales MySQL
echo 2. Asegúrate de que las tablas existan en MySQL:
echo    - Ejecuta MIGRATION.sql en phpMyAdmin
echo 3. Sube esta carpeta al servidor via FTP:
echo    - /public_html/ (raíz)
echo    - /public_html/api/ (subcarpeta)
echo 4. Actualiza la URL en tu React frontend:
echo    - axios.defaults.baseURL = 'https://40figuritas.unr.edu.ar/server-php'
echo 5. Test con curl o navegador:
echo    - https://40figuritas.unr.edu.ar/server-php/users
echo.
echo * Listo para deployment
pause
