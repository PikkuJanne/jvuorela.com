# SPDX-License-Identifier: GPL-2.0-or-later
[CmdletBinding()]
param([ValidateRange(1,65535)][int]$Port = 8088)
$ErrorActionPreference = 'Stop'
$taskRoot = Split-Path $PSScriptRoot -Parent
Push-Location $taskRoot
try {
    New-Item -ItemType Directory -Force -Path 'private' | Out-Null
    $downloads = @(
        @{Url='https://downloads.php.net/~windows/releases/php-8.3.35-nts-Win32-vs16-x64.zip'; Path='private/php.zip'; Hash='25a8e2ac9ff30f1d768d1447c09a600617fa6e6082729f6e95f008b59c91fe45'},
        @{Url='https://downloads.wordpress.org/release/wordpress-7.1.3.zip'; Path='private/wordpress.zip'; Hash='eb58a90ed55147f90f7bd55d0d3334960a69b7e8a4cc142c8ddfea8965218227'},
        @{Url='https://downloads.wordpress.org/plugin/sqlite-database-integration.3.1.0.zip'; Path='private/sqlite.zip'; Hash='e260e3ada78676f1c0f4e09eb9ebcb9ecce4d4b85f0c6e5a70fb9a93f5b9847f'},
        @{Url='https://github.com/wp-cli/wp-cli/releases/download/v2.12.0/wp-cli-2.12.0.phar'; Path='private/wp-cli.phar'; Hash='ce34ddd838f7351d6759068d09793f26755463b4a4610a5a5c0a97b68220d85c'}
    )
    foreach ($item in $downloads) {
        if (!(Test-Path -LiteralPath $item.Path)) { Invoke-WebRequest -Uri $item.Url -OutFile $item.Path }
        if ((Get-FileHash -LiteralPath $item.Path -Algorithm SHA256).Hash.ToLowerInvariant() -ne $item.Hash) { throw "Checksum mismatch: $($item.Path)" }
    }
    if (!(Test-Path 'private/php/php.exe')) { Expand-Archive -LiteralPath 'private/php.zip' -DestinationPath 'private/php' }
    if (!(Test-Path 'private/wordpress/wp-load.php')) { Expand-Archive -LiteralPath 'private/wordpress.zip' -DestinationPath 'private' }
    if (!(Test-Path 'private/wordpress/wp-content/plugins/sqlite-database-integration')) { Expand-Archive -LiteralPath 'private/sqlite.zip' -DestinationPath 'private/wordpress/wp-content/plugins' }
    @'
extension_dir="ext"
extension=mysqli
extension=pdo_sqlite
extension=sqlite3
extension=mbstring
extension=gd
extension=openssl
extension=curl
extension=fileinfo
extension=zip
memory_limit=512M
upload_max_filesize=20M
post_max_size=24M
max_execution_time=120
error_reporting=E_ALL
log_errors=On
'@ | Set-Content -LiteralPath 'private/php/php.ini' -Encoding utf8
    if (!(Test-Path 'private/wordpress/wp-content/db.php')) { Copy-Item -LiteralPath 'private/wordpress/wp-content/plugins/sqlite-database-integration/db.copy' -Destination 'private/wordpress/wp-content/db.php' }
    if (!(Test-Path 'private/wordpress/wp-config.php')) {
        & private/php/php.exe private/wp-cli.phar config create --path=private/wordpress --dbname=local --dbuser=local --skip-check
        if ($LASTEXITCODE) { throw 'Local configuration failed' }
    }
    & private/php/php.exe private/wp-cli.phar core is-installed --path=private/wordpress 2>$null
    if ($LASTEXITCODE) {
        $taskPassword = [Convert]::ToBase64String([Security.Cryptography.RandomNumberGenerator]::GetBytes(32))
        $previousTaskPassword = $env:JVT_LOCAL_PASSWORD
        $previousTaskPort = $env:JVT_LOCAL_PORT
        try {
            $env:JVT_LOCAL_PASSWORD = $taskPassword
            $env:JVT_LOCAL_PORT = $Port
            & private/php/php.exe private/wp-cli.phar eval-file scripts/install-local.php --path=private/wordpress --skip-wordpress
            if ($LASTEXITCODE) { throw 'Local WordPress install failed' }
        } finally {
            if ($null -eq $previousTaskPassword) { Remove-Item Env:JVT_LOCAL_PASSWORD -ErrorAction SilentlyContinue } else { $env:JVT_LOCAL_PASSWORD = $previousTaskPassword }
            if ($null -eq $previousTaskPort) { Remove-Item Env:JVT_LOCAL_PORT -ErrorAction SilentlyContinue } else { $env:JVT_LOCAL_PORT = $previousTaskPort }
        }
        & private/php/php.exe private/wp-cli.phar core is-installed --path=private/wordpress 2>$null
        if ($LASTEXITCODE) { throw 'Local WordPress is not installed; setup cannot report ready' }
    }
    Copy-Item -LiteralPath 'theme/jvuorela-tools' -Destination 'private/wordpress/wp-content/themes' -Recurse -Force
    & private/php/php.exe private/wp-cli.phar theme activate jvuorela-tools --path=private/wordpress
    if ($LASTEXITCODE) { throw 'Local theme activation failed' }
    & private/php/php.exe private/wp-cli.phar option update blog_public 0 --path=private/wordpress
    if ($LASTEXITCODE) { throw 'Local preview indexing setting failed' }
    & private/php/php.exe private/wp-cli.phar option update permalink_structure '/%postname%/' --path=private/wordpress
    if ($LASTEXITCODE) { throw 'Local pretty permalink setup failed' }
    Write-Output 'Local WordPress ready. No project content was seeded or published. Read docs/LOCAL_SETUP.md.'
} finally { Pop-Location }
