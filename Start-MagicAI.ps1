$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot
$phpRuntime = Join-Path (Split-Path $PSScriptRoot -Parent) 'MagicAI-Package\php85\php.exe'
& $phpRuntime artisan serve --host=127.0.0.1 --port=8020
