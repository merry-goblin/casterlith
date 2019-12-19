@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../atoum/atoum/bin/atoum
php "%BIN_TARGET%" %*
