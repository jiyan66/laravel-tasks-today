param([switch]$SeedDemo)
$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot
. (Join-Path $PSScriptRoot 'scripts\Use-TaskPhp.ps1')
if (-not (Test-Path 'routes\auth.php')) { throw 'For TSA3 upgrades use APPLY-TSA4.ps1 from the update ZIP. This setup is for a completed TSA4 source checkout.' }
if (-not (Test-Path 'composer.lock') -or -not (Test-Path 'package-lock.json')) { throw 'A complete TSA4 checkout must include composer.lock and package-lock.json.' }
if (-not (Test-Path '.env')) { Copy-Item '.env.example' '.env' }
if (-not (Test-Path 'database\database.sqlite')) { New-Item -ItemType File 'database\database.sqlite' | Out-Null }
& composer install --no-interaction --prefer-dist
if ($LASTEXITCODE -ne 0) { throw 'Composer installation failed.' }
$envText = [System.IO.File]::ReadAllText((Join-Path $PSScriptRoot '.env'))
if ($envText -match '(?m)^APP_KEY=\s*$') {
    & php artisan key:generate
    if ($LASTEXITCODE -ne 0) { throw 'Key generation failed.' }
}
$npmTool = Get-Command npm.cmd -ErrorAction SilentlyContinue
if (-not $npmTool) { $npmTool = Get-Command npm -ErrorAction Stop }
& $npmTool.Source ci
if ($LASTEXITCODE -ne 0) { throw 'npm ci failed.' }
& $npmTool.Source run build
if ($LASTEXITCODE -ne 0) { throw 'Frontend build failed.' }
& php artisan optimize:clear
if ($LASTEXITCODE -ne 0) { throw 'Cache cleanup failed.' }
& php artisan migrate --no-interaction
if ($LASTEXITCODE -ne 0) { throw 'Migration failed.' }
# Explicit opt-in: seeding refreshes demo task statuses/dates, not existing passwords or archive flags.
if ($SeedDemo) {
    & php artisan db:seed --no-interaction
    if ($LASTEXITCODE -ne 0) { throw 'Demo seeding failed.' }
}
& powershell -NoProfile -ExecutionPolicy Bypass -File (Join-Path $PSScriptRoot 'VERIFY.ps1')
if ($LASTEXITCODE -ne 0) { throw 'Verification failed.' }
Write-Host 'Setup finished. Run RUN-TSA4.ps1.' -ForegroundColor Green
