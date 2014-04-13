REM ***********************************************************
REM THIS FILE SHOULD BE PLACED AND RUN FROM C:\Server\bin\tools
REM ***********************************************************

rem Kill if it is running
process -k php.exe

rem start php dev server
start "localhost:91-WPN-PHP-Dev Server" /MIN ../php/php -S localhost:91 -t ../../www/wpn-nano-control

rem start WPN-XM nano Control Panel
start http://localhost:91
