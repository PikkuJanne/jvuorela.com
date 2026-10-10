# SPDX-License-Identifier: GPL-2.0-or-later
# Exact-package rehearsal on a newly allocated loopback installation only.
[CmdletBinding()]
param([string]$ArtifactDirectory = 'build/dev', [int]$Port = 8089)
$ErrorActionPreference = 'Stop'
$taskRoot = Split-Path $PSScriptRoot -Parent
$php = Join-Path $taskRoot 'private/php/php.exe'
$wpCli = Join-Path $taskRoot 'private/wp-cli.phar'
$runRoot = Join-Path $taskRoot ('private/rehearsal/run-' + [Guid]::NewGuid().ToString('N'))
$wpRoot = Join-Path $runRoot 'wordpress'
$server = $null
$previousWpRoot = $env:JVT_WP_ROOT
$previousPassword = $env:JVT_REHEARSAL_PASSWORD
$previousInstallRoot = $env:JVT_REHEARSAL_WP_ROOT
$previousPort = $env:JVT_REHEARSAL_PORT
$previousSetup = $env:JVT_REHEARSAL_SETUP
$phase = 'preflight'
$report = [ordered]@{schema_version=1; observed_at_utc=[DateTime]::UtcNow.ToString('o'); result='running'; harness_sha256=(Get-FileHash -LiteralPath $PSCommandPath -Algorithm SHA256).Hash.ToLowerInvariant(); environment=@{php='8.3.35';wordpress='7.1.3';wp_cli='2.12.0';sqlite_adapter='3.1.0';target='new isolated loopback installation'}; checks=@(); primary_instance_mutation_commands=0; limits=@('Local SQLite/loopback evidence does not establish IONOS MySQL/PHP or hosted backup acceptance.','Dirty development packages are provisional; the final clean source/package must be rehearsed separately.','No credentials, local paths, database dumps, attachment IDs or original artwork are public evidence.')}
$previousEvidence = Join-Path $taskRoot 'evidence/deployment-rehearsal.json'
if (Test-Path -LiteralPath $previousEvidence) {
    $previous = Get-Content -LiteralPath $previousEvidence -Raw | ConvertFrom-Json
    $report.previous_attempts = @($previous.previous_attempts) + @(@{observed_at_utc=$previous.observed_at_utc;result=$previous.result;source=$previous.source;packages=$previous.packages;passed_checks=@($previous.checks | Where-Object result -eq pass).Count;failure_phase=$previous.failure_phase})
    $report.previous_attempts = @($report.previous_attempts | Where-Object { $null -ne $_ })
}

function Add-Check([string]$Name, [bool]$Passed) {
    $script:report.checks += @{name=$Name;result=$(if ($Passed) {'pass'} else {'fail'})}
    if (!$Passed) { throw "Rehearsal check failed: $Name" }
}

function Invoke-RehearsalWp([string[]]$Arguments, [switch]$BeforeInstall) {
    if (![IO.Path]::GetFullPath($script:wpRoot).StartsWith([IO.Path]::GetFullPath($script:runRoot) + [IO.Path]::DirectorySeparatorChar)) { throw 'Unsafe WordPress target.' }
    $cliArguments = @($script:wpCli, ('--path=' + $script:wpRoot))
    if (!$BeforeInstall) { $cliArguments += '--user=rehearsal-owner' }
    $cliArguments += $Arguments
    $result = & $script:php @cliArguments 2>&1
    $nativeExit = $LASTEXITCODE
    $result | Set-Content -LiteralPath (Join-Path $script:runRoot ('wp-' + [Guid]::NewGuid().ToString('N') + '.txt')) -Encoding utf8
    if ($nativeExit) { throw "WP-CLI failed during $script:phase; details retained privately." }
    return ($result -join [Environment]::NewLine)
}

function Get-TreeRecord([string]$Directory) {
    $records = @()
    foreach ($file in (Get-ChildItem -LiteralPath $Directory -Recurse -File -Force | Sort-Object FullName)) {
        $records += @{path=$file.FullName.Substring($Directory.Length).TrimStart('\','/').Replace('\','/');sha256=(Get-FileHash -LiteralPath $file.FullName -Algorithm SHA256).Hash.ToLowerInvariant();bytes=$file.Length}
    }
    $serialized = ($records | ConvertTo-Json -Depth 6 -Compress)
    $hash = [Security.Cryptography.SHA256]::HashData([Text.Encoding]::UTF8.GetBytes($serialized))
    return @{files=$records.Count; sha256=[Convert]::ToHexString($hash).ToLowerInvariant()}
}

function Assert-WithinFreshWordPress([string]$Path) {
    $resolved = [IO.Path]::GetFullPath($Path)
    $boundary = [IO.Path]::GetFullPath($script:wpRoot) + [IO.Path]::DirectorySeparatorChar
    if (!$resolved.StartsWith($boundary, [StringComparison]::OrdinalIgnoreCase)) { throw 'Restore target escapes the newly created rehearsal WordPress directory.' }
    if ($resolved.StartsWith([IO.Path]::GetFullPath((Join-Path $script:taskRoot 'private/wordpress')) + [IO.Path]::DirectorySeparatorChar, [StringComparison]::OrdinalIgnoreCase)) { throw 'Primary installation is protected.' }
}

function Expand-CandidateArchive([string]$Archive, [string]$Destination, [string]$RootName) {
    $zip = [IO.Compression.ZipFile]::OpenRead($Archive)
    try {
        foreach ($entry in $zip.Entries) {
            if (!$entry.FullName.StartsWith($RootName + '/') -or $entry.FullName.Contains('\') -or $entry.FullName.Contains(':') -or ($entry.FullName.Split('/') -contains '..')) { throw 'Unsafe candidate ZIP entry.' }
        }
    } finally { $zip.Dispose() }
    if (Test-Path -LiteralPath $Destination) { throw 'Existing extraction target preserved.' }
    Expand-Archive -LiteralPath $Archive -DestinationPath $Destination
}

function Start-RehearsalServer {
    $env:JVT_WP_ROOT = $script:wpRoot
    $arguments = @('-S', ('127.0.0.1:' + $script:Port), '-t', ('"' + $script:wpRoot + '"'), ('"' + $script:router + '"'))
    $script:server = Start-Process -FilePath $script:php -ArgumentList $arguments -WindowStyle Hidden -PassThru -RedirectStandardOutput (Join-Path $script:runRoot ('server-' + [Guid]::NewGuid().ToString('N') + '.stdout.txt')) -RedirectStandardError (Join-Path $script:runRoot ('server-' + [Guid]::NewGuid().ToString('N') + '.stderr.txt'))
    $ready = $false
    for ($attempt=0; $attempt -lt 30; $attempt++) {
        if ($script:server.HasExited) { throw 'Owned rehearsal server exited.' }
        try {
            $client = [Net.Http.HttpClient]::new()
            $response = $client.GetAsync('http://127.0.0.1:' + $script:Port + '/wp-login.php').GetAwaiter().GetResult()
            $ready = [int]$response.StatusCode -eq 200
            $response.Dispose(); $client.Dispose()
            if ($ready) { break }
        } catch { }
        Start-Sleep -Milliseconds 200
    }
    Add-Check 'separate loopback server and signed-out login endpoint ready' $ready
}

function Stop-RehearsalServer {
    if ($script:server -and !$script:server.HasExited) {
        Stop-Process -Id $script:server.Id -Force
        $script:server.WaitForExit(5000) | Out-Null
    }
    $script:server = $null
}

function Get-HttpChecks($Snapshot) {
    $client = [Net.Http.HttpClient]::new()
    $pageHashes = @{}
    try {
        foreach ($page in $Snapshot.routes) {
            $response = $client.GetAsync($page.url).GetAwaiter().GetResult()
            $html = $response.Content.ReadAsStringAsync().GetAwaiter().GetResult()
            Add-Check ('signed-out route ' + $page.slug + ' HTTP ' + $page.status) ([int]$response.StatusCode -eq $page.status -and !$html.Contains('id="wpadminbar"') -and !$html.Contains('{{asset:') -and !$html.Contains('{{page:'))
            $footer = [regex]::Match($html, '<footer\b[^>]*>(.*?)</footer>', [Text.RegularExpressions.RegexOptions]::Singleline).Groups[1].Value
            $links = [regex]::Matches($footer, '<a\b[^>]*href=["''](?<url>[^"'']+)["''][^>]*>(?<label>.*?)</a>', [Text.RegularExpressions.RegexOptions]::Singleline)
            $canonicalFooter = ![string]::IsNullOrWhiteSpace($footer) -and !$footer.Contains('?page_id=')
            foreach ($expected in $Snapshot.footer_links) {
                $matching = @($links | Where-Object {
                    $url = [Net.WebUtility]::HtmlDecode($_.Groups['url'].Value)
                    $label = [Net.WebUtility]::HtmlDecode([regex]::Replace($_.Groups['label'].Value, '<[^>]+>', '')).Trim()
                    $url -eq $expected.url -and $label -eq $expected.title
                })
                $canonicalFooter = $canonicalFooter -and $matching.Count -eq 1
            }
            Add-Check ('canonical Impressum and Privacy footer links on ' + $page.slug) $canonicalFooter
            if ($page.slug -eq 'impressum') {
                $text = [Net.WebUtility]::HtmlDecode([regex]::Replace($html, '<[^>]+>', ' '))
                $text = [regex]::Replace($text, '\s+', ' ')
                $ownerCopy = $true
                foreach ($expectedText in @('Angaben gemäß § 18 Abs. 1 Medienstaatsvertrag (MStV)', 'Janne Vuorela', 'Marie-Curie-Allee 83', '10315 Berlin', 'Deutschland', 'Kontakt', 'Über diese Website', 'Diese Website ist ein nichtkommerzielles Privatprojekt von Janne Vuorela.', 'Sie dient der Vorstellung und Bereitstellung eigener Open-Source-Software.')) {
                    $ownerCopy = $ownerCopy -and $text.Contains($expectedText)
                }
                $ownerCopy = $ownerCopy -and [regex]::IsMatch($html, '<a\b[^>]*href=["'']mailto:pikkujean@gmail\.com["''][^>]*>pikkujean@gmail\.com</a>')
                Add-Check 'signed-out Impressum renders supplied owner text and mailto email' $ownerCopy
            }
            $pageHashes[$page.slug] = [Convert]::ToHexString([Security.Cryptography.SHA256]::HashData([Text.Encoding]::UTF8.GetBytes($html))).ToLowerInvariant()
            $response.Dispose()
        }
        $nativeMedia = 0
        foreach ($media in $Snapshot.media) {
            $response = $client.GetAsync($media.url).GetAwaiter().GetResult()
            $bytes = $response.Content.ReadAsByteArrayAsync().GetAwaiter().GetResult()
            $sha = [Convert]::ToHexString([Security.Cryptography.SHA256]::HashData($bytes)).ToLowerInvariant()
            if ([int]$response.StatusCode -ne 200 -or $sha -ne $media.sha256) { throw 'Signed-out native Media Library response differs from traced original.' }
            $nativeMedia++
            $response.Dispose()
        }
        Add-Check 'all 32 native media originals serve exact approved derivative bytes signed out' ($nativeMedia -eq 32)
        $resizedMedia = 0
        foreach ($media in $Snapshot.resized_media) {
            $response = $client.GetAsync($media.url).GetAwaiter().GetResult()
            $bytes = $response.Content.ReadAsByteArrayAsync().GetAwaiter().GetResult()
            $sha = [Convert]::ToHexString([Security.Cryptography.SHA256]::HashData($bytes)).ToLowerInvariant()
            if ([int]$response.StatusCode -ne 200 -or $sha -ne $media.sha256) { throw 'Signed-out proportional native image differs from its generated attachment file.' }
            $resizedMedia++
            $response.Dispose()
        }
        Add-Check 'all 14 proportional native character480 and avatar360 files serve signed out' ($resizedMedia -eq 14)
        return $pageHashes
    } finally { $client.Dispose() }
}

Push-Location $taskRoot
try {
    if (Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue) { throw 'Rehearsal port is already owned; existing process preserved.' }
    $pins = @{'private/php.zip'='25a8e2ac9ff30f1d768d1447c09a600617fa6e6082729f6e95f008b59c91fe45';'private/wordpress.zip'='eb58a90ed55147f90f7bd55d0d3334960a69b7e8a4cc142c8ddfea8965218227';'private/sqlite.zip'='e260e3ada78676f1c0f4e09eb9ebcb9ecce4d4b85f0c6e5a70fb9a93f5b9847f';'private/wp-cli.phar'='ce34ddd838f7351d6759068d09793f26755463b4a4610a5a5c0a97b68220d85c'}
    foreach ($path in $pins.Keys) {
        if (!(Test-Path -LiteralPath $path) -or (Get-FileHash -LiteralPath $path -Algorithm SHA256).Hash.ToLowerInvariant() -ne $pins[$path]) { throw 'Pinned development runtime unavailable; run setup-local.ps1 first.' }
    }
    Add-Check 'official pinned existing runtime downloads verified' $true
    New-Item -ItemType Directory -Path $runRoot | Out-Null
    $artifactRoot = (Resolve-Path -LiteralPath $ArtifactDirectory).Path
    $index = Get-Content -LiteralPath (Join-Path $artifactRoot 'artifacts.json') -Raw | ConvertFrom-Json
    $themeArtifact = @($index.artifacts | Where-Object { $_.name -match '^jvuorela-tools-[0-9].*\.zip$' })
    $setupArtifact = @($index.artifacts | Where-Object { $_.name -match '^jvuorela-tools-content-media-[0-9].*\.zip$' })
    if ($themeArtifact.Count -ne 1 -or $setupArtifact.Count -ne 1) { throw 'Expected exactly one theme and setup ZIP.' }
    $packages = Join-Path $runRoot 'packages'
    New-Item -ItemType Directory -Path $packages | Out-Null
    foreach ($artifact in @($themeArtifact[0],$setupArtifact[0])) {
        $source = Join-Path $artifactRoot $artifact.name
        Add-Check ('artifact checksum ' + $artifact.name) ((Get-FileHash -LiteralPath $source -Algorithm SHA256).Hash.ToLowerInvariant() -eq $artifact.sha256)
        Copy-Item -LiteralPath $source -Destination (Join-Path $packages $artifact.name)
    }
    $report.packages = @($themeArtifact[0],$setupArtifact[0])
    $report.source = $index.source
    $report.website_version = $index.website_version
    $report.package_is_provisional = [bool]$index.source.dirty
    $setupExtract = Join-Path $runRoot 'setup'
    Expand-CandidateArchive (Join-Path $packages $setupArtifact[0].name) $setupExtract 'jvuorela-tools-setup'
    $setupRoot = Join-Path $setupExtract 'jvuorela-tools-setup'
    $router = Join-Path $setupRoot 'scripts/router.php'
    Expand-Archive -LiteralPath 'private/wordpress.zip' -DestinationPath $runRoot
    Expand-Archive -LiteralPath 'private/sqlite.zip' -DestinationPath (Join-Path $wpRoot 'wp-content/plugins')
    Copy-Item -LiteralPath (Join-Path $wpRoot 'wp-content/plugins/sqlite-database-integration/db.copy') -Destination (Join-Path $wpRoot 'wp-content/db.php')
    $phase = 'fresh configuration'
    Invoke-RehearsalWp @('config','create','--dbname=rehearsal','--dbuser=rehearsal','--skip-check','--skip-salts') -BeforeInstall | Out-Null
    $configPath = Join-Path $wpRoot 'wp-config.php'
    $config = Get-Content -LiteralPath $configPath -Raw
    $config = $config.Replace("define( 'WP_DEBUG', false );", "define( 'WP_DEBUG', true );")
    $config = $config.Replace('/* That''s all, stop editing! Happy publishing. */', "define('DISABLE_WP_CRON', true);`ndefine('WP_ENVIRONMENT_TYPE', 'local');`ndefine('WP_DEBUG_LOG', true);`ndefine('WP_DEBUG_DISPLAY', false);`n/* That's all, stop editing! Happy publishing. */")
    Set-Content -LiteralPath $configPath -Value $config -Encoding utf8
    $env:JVT_REHEARSAL_PASSWORD = [Convert]::ToBase64String([Security.Cryptography.RandomNumberGenerator]::GetBytes(32))
    $env:JVT_REHEARSAL_WP_ROOT = $wpRoot
    $env:JVT_REHEARSAL_PORT = $Port
    $install = @'
<?php
if (!defined('WP_CLI') || !WP_CLI || !getenv('JVT_REHEARSAL_PASSWORD')) { exit(1); }
define('WP_INSTALLING', true);
$_SERVER['HTTP_HOST'] = '127.0.0.1:' . (int) getenv('JVT_REHEARSAL_PORT');
$_SERVER['SERVER_NAME'] = '127.0.0.1';
$_SERVER['SERVER_PORT'] = (int) getenv('JVT_REHEARSAL_PORT');
$_SERVER['REQUEST_URI'] = '/';
require getenv('JVT_REHEARSAL_WP_ROOT') . '/wp-load.php';
require ABSPATH . 'wp-admin/includes/upgrade.php';
if (is_blog_installed()) { WP_CLI::error('Existing site preserved; fresh installation refused.'); }
wp_install('jvuorela.com', 'rehearsal-owner', 'owner@example.invalid', false, '', getenv('JVT_REHEARSAL_PASSWORD'));
$url = 'http://127.0.0.1:' . (int) getenv('JVT_REHEARSAL_PORT');
update_option('siteurl', $url); update_option('home', $url);
update_option('blog_public', 0); update_option('permalink_structure', '/%postname%/');
update_option('wp_page_for_privacy_policy', 0);
WP_CLI::success('Fresh isolated site installed without printing credentials.');
'@
    $installPath = Join-Path $runRoot 'install.php'
    Set-Content -LiteralPath $installPath -Value $install -Encoding utf8
    Invoke-RehearsalWp @('eval-file',$installPath,'--skip-wordpress') -BeforeInstall | Out-Null
    $phase = 'exact theme package install'
    Invoke-RehearsalWp @('theme','install',(Join-Path $packages $themeArtifact[0].name),'--activate') | Out-Null
    $themePath = Join-Path $wpRoot 'wp-content/themes/jvuorela-tools'
    $themeZip = [IO.Compression.ZipFile]::OpenRead((Join-Path $packages $themeArtifact[0].name))
    try {
        foreach ($entry in $themeZip.Entries) {
            $relative = $entry.FullName.Substring('jvuorela-tools/'.Length)
            $installed = Join-Path $themePath $relative
            $stream = $entry.Open()
            try { $archiveSha = [Convert]::ToHexString([Security.Cryptography.SHA256]::HashData($stream)).ToLowerInvariant() } finally { $stream.Dispose() }
            if (!(Test-Path -LiteralPath $installed) -or (Get-FileHash -LiteralPath $installed -Algorithm SHA256).Hash.ToLowerInvariant() -ne $archiveSha) { throw 'Installed theme differs from exact ZIP entry.' }
        }
        Add-Check 'every installed theme file equals its exact theme ZIP entry' $true
    } finally { $themeZip.Dispose() }
    $phase = 'portable content media initialization'
    $importer = Join-Path $setupRoot 'scripts/import-content.php'
    $plan = Invoke-RehearsalWp @('eval-file',$importer) | ConvertFrom-Json
    Add-Check 'portable package initial plan is read-only and conflict-free for 11 missing drafts' (!$plan.writes -and @($plan.conflicts).Count -eq 0 -and @($plan.pages | Where-Object state -eq create).Count -eq 11)
    $apply = Invoke-RehearsalWp @('eval-file',$importer,'apply') | ConvertFrom-Json
    Add-Check 'portable package imports 11 drafts and 32 native attachments plus theme favicon' (@($apply.pages | Where-Object state -eq created_draft).Count -eq 11 -and @($apply.media | Where-Object state -eq uploaded).Count -eq 32 -and @($apply.media | Where-Object state -eq theme_runtime_file).Count -eq 1)
    $rerun = Invoke-RehearsalWp @('eval-file',$importer) | ConvertFrom-Json
    Add-Check 'portable package rerun preserves all drafts/media without duplication' (!$rerun.writes -and @($rerun.conflicts).Count -eq 0 -and @($rerun.pages | Where-Object state -eq unchanged).Count -eq 11)
    $configure = Invoke-RehearsalWp @('eval-file',$importer,'configure-apply') | ConvertFrom-Json
    Add-Check 'explicit native front privacy icon and menus configured without Page publication' ($configure.writes -and @($configure.conflicts).Count -eq 0)
    $drafts = Invoke-RehearsalWp @('post','list','--post_type=page','--post_status=any','--meta_key=_jvt_content_id','--fields=post_status','--format=json') | ConvertFrom-Json
    Add-Check 'all 11 native project Pages remain drafts after import and configuration' (@($drafts).Count -eq 11 -and @($drafts | Where-Object post_status -ne draft).Count -eq 0)
    $publish = @'
<?php
if (!defined('WP_CLI') || !WP_CLI || !current_user_can('manage_options') || wp_parse_url(home_url('/'), PHP_URL_HOST) !== '127.0.0.1') { exit(1); }
foreach (get_posts(array('post_type'=>'page','post_status'=>'draft','numberposts'=>-1,'meta_key'=>'_jvt_content_id')) as $post) { wp_update_post(array('ID'=>$post->ID,'post_status'=>'publish')); }
update_option('blog_public',0);
WP_CLI::success('Rehearsal Pages published only on isolated loopback.');
'@
    $publishPath = Join-Path $runRoot 'publish.php'
    Set-Content -LiteralPath $publishPath -Value $publish -Encoding utf8
    Invoke-RehearsalWp @('eval-file',$publishPath) | Out-Null
    $probe = @'
<?php
define('JVT_IMPORT_NO_RUN',true);
require getenv('JVT_REHEARSAL_SETUP') . '/scripts/import-content.php';
$result=array('routes'=>array(),'media'=>array(),'resized_media'=>array(),'custom_sizes'=>array('character'=>0,'avatar'=>0),'footer_links'=>array(array('title'=>'Impressum','url'=>home_url('/impressum/')),array('title'=>'Privacy','url'=>home_url('/privacy/'))),'pages'=>count(jvt_import_marker_index('_jvt_content_id')),'native_media'=>count(jvt_import_marker_index('_jvt_asset_slot')),'editorial'=>array());
foreach (jvt_import_marker_index('_jvt_content_id') as $marker=>$matches) {
  if(count($matches)!==1) { WP_CLI::error('Duplicate page marker.'); }
  $id=$matches[0]['id']; $post=get_post($id);
  if($post->post_status!=='publish' || strpos($post->post_content,'{{')!==false) { WP_CLI::error('Unpublished or unresolved project Page.'); }
  $result['editorial'][$marker]=jvt_import_page_state($id);
  if($marker!=='not-found') { $result['routes'][]=array('slug'=>$marker,'url'=>get_permalink($id),'status'=>200); }
}
$result['routes'][]=array('slug'=>'missing-route','url'=>home_url('/rehearsal-route-that-does-not-exist/'),'status'=>404);
foreach (jvt_import_marker_index('_jvt_asset_slot') as $marker=>$matches) {
  if(count($matches)!==1) { WP_CLI::error('Duplicate asset marker.'); }
  $id=$matches[0]['id']; $file=get_attached_file($id); $sha=get_post_meta($id,'_jvt_asset_sha256',true);
  if(!is_file($file) || hash_file('sha256',$file)!==$sha) { WP_CLI::error('Native attachment differs from approved derivative.'); }
  $meta=wp_get_attachment_metadata($id);
  if(empty($meta['width']) || empty($meta['height'])) { WP_CLI::error('Missing native image dimensions.'); }
  $character=str_ends_with($marker,'-character'); $avatar=str_ends_with($marker,'-avatar');
  if(!$character && isset($meta['sizes']['jvt-character-small'])) { WP_CLI::error('Unrelated attachment has a character-specific size.'); }
  if(!$avatar && isset($meta['sizes']['jvt-avatar-small'])) { WP_CLI::error('Unrelated attachment has an avatar-specific size.'); }
  if($character || $avatar) {
    $kind=$character?'character':'avatar'; $size=$character?'jvt-character-small':'jvt-avatar-small'; $width=$character?480:360;
    $small=$meta['sizes'][$size]??null;
    if(!$small || (int)$small['width']!==$width || abs((float)$small['height']-($meta['height']*$width/$meta['width']))>1) { WP_CLI::error('Missing or cropped native proportional project image.'); }
    $small_file=dirname($file).'/'.$small['file'];
    $actual=is_file($small_file)?getimagesize($small_file):false;
    if(!$actual || $actual[0]!==$small['width'] || $actual[1]!==$small['height']) { WP_CLI::error('Native generated image dimensions disagree with metadata.'); }
    $src=wp_get_attachment_image_src($id,$size);
    if(!$src || $src[1]!==$small['width'] || $src[2]!==$small['height']) { WP_CLI::error('Native attachment source does not resolve proportional image.'); }
    $result['resized_media'][]=array('url'=>$src[0],'sha256'=>hash_file('sha256',$small_file));
    $result['custom_sizes'][$kind]++;
  }
  $result['media'][]=array('url'=>wp_get_attachment_url($id),'sha256'=>$sha,'file'=>$file);
}
WP_CLI::line(wp_json_encode($result));
'@
    $env:JVT_REHEARSAL_SETUP = $setupRoot
    $probePath = Join-Path $runRoot 'probe.php'
    Set-Content -LiteralPath $probePath -Value $probe -Encoding utf8
    $snapshot = Invoke-RehearsalWp @('eval-file',$probePath) | ConvertFrom-Json
    Add-Check 'fresh native Pages and media have no unresolved tokens or missing dimensions' ($snapshot.pages -eq 11 -and $snapshot.native_media -eq 32)
    Add-Check 'seven character480 and seven avatar360 native sizes are proportional and attachment-specific' ($snapshot.custom_sizes.character -eq 7 -and $snapshot.custom_sizes.avatar -eq 7)
    Start-RehearsalServer
    $httpBefore = Get-HttpChecks $snapshot
    Stop-RehearsalServer
    $phase = 'consistent stopped-server backup'
    $backupRoot = Join-Path $runRoot 'backups'
    New-Item -ItemType Directory -Path $backupRoot | Out-Null
    $restoreParts = @{'database'=(Join-Path $wpRoot 'wp-content/database');'uploads'=(Join-Path $wpRoot 'wp-content/uploads');'theme'=$themePath}
    $beforeTrees = @{}
    foreach ($name in $restoreParts.Keys) {
        Assert-WithinFreshWordPress $restoreParts[$name]
        $beforeTrees[$name] = Get-TreeRecord $restoreParts[$name]
        Copy-Item -LiteralPath $restoreParts[$name] -Destination (Join-Path $backupRoot $name) -Recurse
        Add-Check ('private backup readable and byte-identical for ' + $name) ((Get-TreeRecord (Join-Path $backupRoot $name)).sha256 -eq $beforeTrees[$name].sha256)
    }
    Copy-Item -LiteralPath $configPath -Destination (Join-Path $backupRoot 'wp-config.php')
    $configSha = (Get-FileHash -LiteralPath $configPath -Algorithm SHA256).Hash.ToLowerInvariant()
    $phase = 'intentional isolated rollback fault'
    $mutation = @'
<?php
define('JVT_IMPORT_NO_RUN',true); require getenv('JVT_REHEARSAL_SETUP') . '/scripts/import-content.php';
$index=jvt_import_marker_index('_jvt_content_id'); wp_update_post(array('ID'=>$index['home'][0]['id'],'post_title'=>'Rollback rehearsal sentinel'));
WP_CLI::success('Only the fresh rehearsal Home title changed.');
'@
    $mutationPath = Join-Path $runRoot 'mutate.php'
    Set-Content -LiteralPath $mutationPath -Value $mutation -Encoding utf8
    Invoke-RehearsalWp @('eval-file',$mutationPath) | Out-Null
    $ownedMedia = $snapshot.media[0].file
    Assert-WithinFreshWordPress $ownedMedia
    if (![IO.Path]::GetFullPath($ownedMedia).StartsWith([IO.Path]::GetFullPath($restoreParts['uploads']) + [IO.Path]::DirectorySeparatorChar)) { throw 'Fault target is not an owned rehearsal upload.' }
    Remove-Item -LiteralPath $ownedMedia
    Add-Content -LiteralPath (Join-Path $themePath 'style.css') -Value '/* isolated rollback rehearsal sentinel */'
    Add-Check 'intentional fresh-fixture database media and theme faults differ from backup' ((Get-TreeRecord $restoreParts['database']).sha256 -ne $beforeTrees['database'].sha256 -and !(Test-Path -LiteralPath $ownedMedia) -and (Get-TreeRecord $themePath).sha256 -ne $beforeTrees['theme'].sha256)
    $phase = 'restore only allocated rehearsal database uploads theme and configuration'
    foreach ($name in $restoreParts.Keys) {
        Assert-WithinFreshWordPress $restoreParts[$name]
        Remove-Item -LiteralPath $restoreParts[$name] -Recurse -Force
        Copy-Item -LiteralPath (Join-Path $backupRoot $name) -Destination $restoreParts[$name] -Recurse
        Add-Check ('restored stopped-server tree equals backup for ' + $name) ((Get-TreeRecord $restoreParts[$name]).sha256 -eq $beforeTrees[$name].sha256)
    }
    Assert-WithinFreshWordPress $configPath
    Copy-Item -LiteralPath (Join-Path $backupRoot 'wp-config.php') -Destination $configPath -Force
    Add-Check 'restored private configuration is byte-identical' ((Get-FileHash -LiteralPath $configPath -Algorithm SHA256).Hash.ToLowerInvariant() -eq $configSha)
    $after = Invoke-RehearsalWp @('eval-file',$probePath) | ConvertFrom-Json
    Add-Check 'restored authoritative Page fields equal the pre-backup editorial snapshot' (($snapshot.editorial | ConvertTo-Json -Depth 20 -Compress) -eq ($after.editorial | ConvertTo-Json -Depth 20 -Compress))
    Add-Check 'restored proportional native image metadata and attachment-specific files remain intact' ($after.custom_sizes.character -eq 7 -and $after.custom_sizes.avatar -eq 7)
    Start-RehearsalServer
    $httpAfter = Get-HttpChecks $after
    Add-Check 'all restored signed-out HTML pages equal pre-backup rendered bodies' (($httpBefore | ConvertTo-Json -Compress) -eq ($httpAfter | ConvertTo-Json -Compress))
    Stop-RehearsalServer
    $report.backup_restore = @{result='pass';server_stopped_for_copy=$true;database_tree=$beforeTrees['database'];uploads_tree=$beforeTrees['uploads'];theme_tree=$beforeTrees['theme'];configuration_sha256=$configSha;scope='Only the freshly allocated rehearsal database/uploads/theme/config; originals and primary instance preserved.'}
    $report.native_pages=11; $report.native_media=32; $report.signed_out_routes=11
    $report.native_proportional_images = @{characters_480=7;avatars_360=7;metadata_and_actual_dimensions_verified=$true;signed_out_generated_bytes_verified=$true}
    $report.result = 'pass'
} catch {
    $report.result = 'fail'
    $report.failure_phase = $phase
    $report.failure_summary = 'Rehearsal stopped; private operation details retained. No primary site operation was issued.'
    Write-Warning $report.failure_summary
    Write-Warning $_.Exception.Message
} finally {
    Stop-RehearsalServer
    $env:JVT_WP_ROOT = $previousWpRoot
    $env:JVT_REHEARSAL_PASSWORD = $previousPassword
    $env:JVT_REHEARSAL_WP_ROOT = $previousInstallRoot
    $env:JVT_REHEARSAL_PORT = $previousPort
    $env:JVT_REHEARSAL_SETUP = $previousSetup
    Pop-Location
    $evidenceRoot = Join-Path $taskRoot 'evidence'
    New-Item -ItemType Directory -Path $evidenceRoot -Force | Out-Null
    $report | ConvertTo-Json -Depth 12 | Set-Content -LiteralPath (Join-Path $evidenceRoot 'deployment-rehearsal.json') -Encoding utf8
    $passed = @($report.checks | Where-Object result -eq pass).Count
    $outcome = if ($report.result -eq 'pass') { 'The exact theme ZIP was installed through WP-CLI and every installed entry matched. The separate portable setup ZIP supplied 11 draft Pages, 32 traced native attachments and the theme favicon. Explicit configuration and local publication passed. Supplied owner Impressum text/email and canonical Impressum/Privacy footer links passed on all routes. Seven character480 and seven avatar360 native generated files passed proportional metadata, actual dimensions and signed-out byte checks. The stopped-server private database/uploads/theme/configuration backup restored intentional fixture faults; restored Page fields, tree hashes, signed-out HTML and approved media bytes matched their prior state.' } else { 'The attempt stopped during ' + $report.failure_phase + '. Only the checks explicitly marked pass in the JSON completed; installation/restore acceptance is not claimed. Private diagnostics were retained for repair.' }
    $packageOutcome = if ($report.package_is_provisional) { 'A dirty preview does not represent the eventual clean source release. Rehearse the final clean packages with the same script.' } else { 'This run used the exact checksummed artifacts identified by clean source metadata in the JSON.' }
    $md = "# Exact-package local deployment and rollback rehearsal`n`nSPDX-License-Identifier: GPL-2.0-or-later`n`nResult: **$($report.result)**. $passed checks passed on a new isolated loopback WordPress 7.1.3 / PHP 8.3.35 / development SQLite 3.1.0 installation. The primary preview was not targeted.`n`n$outcome`n`nThe JSON records actual package hashes/source state and every outcome. Backups, credentials, local paths, original artwork and WordPress IDs remain private.`n`nPackage state: provisional development package = $($report.package_is_provisional). $packageOutcome IONOS/MySQL, hosted backups and public cutover remain separate deployment-session evidence.`n"
    Set-Content -LiteralPath (Join-Path $evidenceRoot 'deployment-rehearsal.md') -Value $md -Encoding utf8
}
if ($report.result -ne 'pass') { exit 1 }
Write-Output "Exact-package isolated install and rollback rehearsal passed: $(@($report.checks).Count) checks. Public sanitized evidence saved."
