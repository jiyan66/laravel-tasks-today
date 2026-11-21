# Dot-source this script so its settings apply to the current PowerShell process.
if (Test-Path 'C:\php\php.exe') {
    $env:Path = 'C:\php;' + $env:Path
    if (Test-Path 'C:\php\php.ini') { $env:PHPRC = 'C:\php\php.ini' }
    if (Test-Path 'C:\php\conf.d') { $env:PHP_INI_SCAN_DIR = 'C:\php\conf.d' }
}
