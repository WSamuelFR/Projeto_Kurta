@echo off
setlocal
title Projeto Kurta - Servidor Local

:: Configura o caminho para o PHP na pasta local
set PHP_PATH=%~dp0php
if exist "%~dp0php\PHP\php.exe" set PHP_PATH=%~dp0php\PHP
set PATH=%PHP_PATH%;%PATH%

:: Verifica se o PHP está acessível
php -v >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERRO] PHP não encontrado! 
    echo Por favor, coloque os arquivos do PHP dentro da pasta: %PHP_PATH%
    pause
    exit /b
)

echo [1/3] Rodando Migrations...
php app/database/migrate.php

echo.
echo [2/3] Iniciando Servidor PHP Interno em http://localhost:8000
echo Mantenha esta janela aberta para o projeto funcionar.
echo.

:: Abre o navegador após um pequeno delay
start "" "http://localhost:8000"

:: Garante que o script rode na pasta raiz do projeto
cd /d "%~dp0"

:: Inicia o servidor PHP na raiz (permitindo acesso a /public e /app)
php -S localhost:8000

pause
