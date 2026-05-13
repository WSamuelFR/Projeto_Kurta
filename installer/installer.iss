; Script gerado pelo Antigravity para o projeto Fell.it
#define AppName "Fell.it"
#define AppVersion "2.0.0"
#define AppPublisher "WSamuelFR"
#define AppURL "https://github.com/WSamuelFR/Projeto_Kurta"
#define AppExeName "start_app.vbs"

[Setup]
; NOTA: O valor de AppId identifica exclusivamente esta aplicação. Não use o mesmo valor de AppId em outros instaladores.
AppId={{E9255776-F52D-442D-80AA-18781E75ACFC}
AppName={#AppName}
AppVersion={#AppVersion}
AppPublisher={#AppPublisher}
AppPublisherURL={#AppURL}
AppSupportURL={#AppURL}
AppUpdatesURL={#AppURL}
DefaultDirName={autopf}\{#AppName}
DisableProgramGroupPage=yes
LicenseFile=LICENSE.txt
PrivilegesRequired=admin
; Remova a linha abaixo se você não tiver um ícone .ico pronto
SetupIconFile=logo.ico
Compression=lzma
SolidCompression=yes
WizardStyle=modern
; Para evitar avisos de perigo, o instalador deve ser assinado. Sem assinatura, o Windows mostrará um alerta.
; SignTool=mysigntool

[Languages]
Name: "brazilianportuguese"; MessagesFile: "compiler:Languages\BrazilianPortuguese.isl"

[Tasks]
Name: "desktopicon"; Description: "{cm:CreateDesktopIcon}"; GroupDescription: "{cm:AdditionalIcons}"; Flags: unchecked

[Files]
; Frontend compilado
Source: "..\dist\*"; DestDir: "{app}\dist"; Flags: ignoreversion recursesubdirs createallsubdirs
; Backend e dependências
Source: "..\server\*"; DestDir: "{app}\server"; Flags: ignoreversion recursesubdirs createallsubdirs
; Banco de dados
Source: "..\kurta.db"; DestDir: "{app}"; Flags: ignoreversion
; Scripts de inicialização
Source: "..\launcher.bat"; DestDir: "{app}"; Flags: ignoreversion
Source: "..\start_app.vbs"; DestDir: "{app}"; Flags: ignoreversion
; Ícone para o atalho
Source: "logo.ico"; DestDir: "{app}"; Flags: ignoreversion

[Icons]
Name: "{autoprograms}\{#AppName}"; Filename: "wscript.exe"; Parameters: """{app}\{#AppExeName}"""; IconFilename: "{app}\logo.ico"
Name: "{autodesktop}\{#AppName}"; Filename: "wscript.exe"; Parameters: """{app}\{#AppExeName}"""; IconFilename: "{app}\logo.ico"; Tasks: desktopicon

[Run]
Description: "{cm:LaunchProgram,{#StringChange(AppName, '&', '&&')}}"; Flags: shellexec postinstall skipifsilent; Filename: "wscript.exe"; Parameters: """{app}\{#AppExeName}"""

[Code]
function InitializeSetup(): Boolean;
begin
  Result := True;
  // Aqui poderíamos adicionar uma verificação de Node.js instalada
end;
