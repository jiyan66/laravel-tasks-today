$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot

# Use the standalone PHP installed during the earlier Laravel activity.
if (Test-Path 'C:\php\php.exe') {
    $env:Path = 'C:\php;' + $env:Path
}

Get-Command php -ErrorAction Stop | Out-Null
Get-Command composer -ErrorAction Stop | Out-Null

& php -r 'exit(PHP_VERSION_ID >= 80300 ? 0 : 1);'
if ($LASTEXITCODE -ne 0) { throw 'Laravel 13 requires PHP 8.3 or newer.' }

if (-not (Test-Path '.env')) { Copy-Item '.env.example' '.env' }
if (-not (Test-Path 'database\database.sqlite')) {
    New-Item -ItemType File 'database\database.sqlite' | Out-Null
}

& composer install --prefer-dist
if ($LASTEXITCODE -ne 0) { throw 'Composer installation failed. Read its error above.' }

$envText = Get-Content '.env' -Raw
if ($envText -match '(?m)^APP_KEY=\s*$') {
    & php artisan key:generate
    if ($LASTEXITCODE -ne 0) { throw 'Application key generation failed.' }
}

& php artisan optimize:clear
if ($LASTEXITCODE -ne 0) { throw 'Cache cleanup failed.' }

& php artisan migrate --seed
if ($LASTEXITCODE -ne 0) { throw 'Database setup failed. Read its error above.' }

Write-Host ''
Write-Host 'Setup finished. Run: composer test' -ForegroundColor Green
Write-Host 'Then start the website: php artisan serve'
Write-Host 'Open http://127.0.0.1:8000'
