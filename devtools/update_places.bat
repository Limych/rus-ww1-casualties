@ECHO OFF
SETLOCAL DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/%~n0.php
php "%BIN_TARGET%" %*