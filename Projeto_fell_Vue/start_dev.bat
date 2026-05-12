@echo off
setlocal
title Projeto Kurta V3 - Fullstack Dev

:: Configura o caminho para o PHP na pasta local
set PHP_PATH=%~dp0php
if exist "%~dp0php\PHP\php.exe" set PHP_PATH=%~dp0php\PHP
set PATH=%PHP_PATH%;%PATH%

echo [1/3] Iniciando Backend PHP (Porta 8000)...
start /b php -S localhost:8000

echo [2/3] Verificando dependencias do Frontend...
if not exist "node_modules" (
    echo Instalando pacotes (isso pode demorar na primeira vez)...
    call npm install
)

echo [3/3] Iniciando Frontend Vite...
npm run dev

pause
