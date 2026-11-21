$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot
. (Join-Path $PSScriptRoot 'scripts\Use-TaskPhp.ps1')
& php artisan serve @args
if ($LASTEXITCODE -ne 0) { throw 'Laravel did not start successfully. Read the error above.' }
