@echo off
setlocal
cd /d "%~dp0"

echo Preparando ambiente Fell.it...

:: Verificar se Node.js existe
where node >nul 2>nul
if %errorlevel% neq 0 (
    echo Erro: Node.js nao encontrado. Por favor, instale o Node.js para rodar este aplicativo.
    pause
    exit /b 1
)

:: Configurar o caminho do banco de dados de forma absoluta para o Prisma
set DATABASE_URL=file:%~dp0kurta.db
echo Database path: %DATABASE_URL%

:: Iniciar o backend
echo Iniciando servidor...
:: Criar um log de erro para diagnóstico
cd server
start /b cmd /c "npm start > %TEMP%\fellit_server_log.txt 2>&1"
cd ..

:: Aguardar o servidor subir
echo Aguardando inicializacao (5s)...
timeout /t 5 /nobreak >nul

:: Verificar se o servidor esta rodando (checa se a porta 3000 esta ocupada)
netstat -ano | findstr :3000 >nul
if %errorlevel% neq 0 (
    echo [ERRO] O servidor nao conseguiu iniciar. 
    echo Verifique os logs abaixo:
    if exist %TEMP%\fellit_server_log.txt type %TEMP%\fellit_server_log.txt
    pause
    exit /b 1
)

:: Abrir o navegador
echo Abrindo Fell.it...
start http://localhost:3000
exit
