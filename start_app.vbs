Set WshShell = CreateObject("WScript.Shell")
' Obtém o diretório onde o script VBS está localizado
strPath = Left(WScript.ScriptFullName, InStrRev(WScript.ScriptFullName, "\"))
WshShell.Run chr(34) & strPath & "launcher.bat" & chr(34), 1
Set WshShell = Nothing
