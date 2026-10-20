# ==============================================================================
# Marketplace Shared Hosting Packaging Script
# Generates a clean, reproducible zip package ready for shared cPanel/DirectAdmin
# ==============================================================================

$ErrorActionPreference = "Stop"

$RootDir = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$DistDir = Join-Path $RootDir "dist"
$StagingDir = Join-Path $DistDir "staging_hosting"
$ZipTarget = Join-Path $DistDir "marketplace_shared_hosting.zip"

Write-Host ">>> Starting packaging for Shared Hosting..." -ForegroundColor Cyan

# 1. Create dist directory
if (!(Test-Path $DistDir)) {
    New-Item -ItemType Directory -Path $DistDir | Out-Null
}

# 2. Clean previous staging
if (Test-Path $StagingDir) {
    Remove-Item -Recurse -Force $StagingDir
}
New-Item -ItemType Directory -Path $StagingDir | Out-Null

if (Test-Path $ZipTarget) {
    Remove-Item -Force $ZipTarget
}

# 3. Clear Laravel runtime cache
Write-Host ">>> Clearing Laravel runtime caches..." -ForegroundColor Yellow
& (Join-Path $RootDir "infra\php.bat") (Join-Path $RootDir "backend\artisan") config:clear
& (Join-Path $RootDir "infra\php.bat") (Join-Path $RootDir "backend\artisan") cache:clear
& (Join-Path $RootDir "infra\php.bat") (Join-Path $RootDir "backend\artisan") route:clear
& (Join-Path $RootDir "infra\php.bat") (Join-Path $RootDir "backend\artisan") view:clear

# 4. Re-export SQL schema and sanitized demo seed
Write-Host ">>> Exporting database schema and sanitized demo seed..." -ForegroundColor Yellow
& (Join-Path $RootDir "infra\php.bat") (Join-Path $RootDir "backend\artisan") schema:export
& (Join-Path $RootDir "infra\php.bat") (Join-Path $RootDir "infra\export_demo_seed.php")

# 5. Copy backend files (excluding secrets, git, tests, cache)
Write-Host ">>> Copying backend source and vendor..." -ForegroundColor Yellow
$BackendStaging = Join-Path $StagingDir "backend"
New-Item -ItemType Directory -Path $BackendStaging | Out-Null

$ExcludeFolders = @('.git', 'tests', 'node_modules', '.phpunit.cache')
$ExcludeFiles = @('.env', '.env.testing', 'installed.lock')

# Robocopy for fast, clean copying
$BackendSrc = Join-Path $RootDir "backend"
robocopy $BackendSrc $BackendStaging /E /XD .git tests node_modules .phpunit.cache /XF .env .env.testing installed.lock *.log /NFL /NDL /NJH /NJS /nc /ns /np

# Ensure storage directories exist in staging
$StorageSubdirs = @(
    "storage\app\public",
    "storage\framework\cache\data",
    "storage\framework\sessions",
    "storage\framework\views",
    "storage\logs",
    "bootstrap\cache"
)
foreach ($sub in $StorageSubdirs) {
    $p = Join-Path $BackendStaging $sub
    if (!(Test-Path $p)) {
        New-Item -ItemType Directory -Path $p -Force | Out-Null
    }
}

# 6. Copy root .htaccess, .env.example, database/exports
Write-Host ">>> Copying root configuration and database exports..." -ForegroundColor Yellow
Copy-Item (Join-Path $RootDir ".htaccess") (Join-Path $StagingDir ".htaccess")
Copy-Item (Join-Path $RootDir "backend\.env.example") (Join-Path $StagingDir ".env.example")

$DbStaging = Join-Path $StagingDir "database\exports"
New-Item -ItemType Directory -Path $DbStaging -Force | Out-Null
Copy-Item (Join-Path $RootDir "database\exports\schema.sql") (Join-Path $DbStaging "schema.sql")
Copy-Item (Join-Path $RootDir "database\exports\demo_seed.sql") (Join-Path $DbStaging "demo_seed.sql")

# 7. Create README_DEPLOY.md in package
$DeployReadme = @'
# Bozor Tizimi (Marketplace) - Shared Hosting O'rnatish Paketi

Ushbu paket Apache/LiteSpeed bilan ishlaydigan shared hosting muhitiga (cPanel, DirectAdmin, Plesk) moslashtirilgan.
Hech qanday Node.js yoki daemon talab qilinmaydi.

## 1. Talablar
- PHP 8.4 yoki undan yuqori
- PHP kengaytmalari: pdo_mysql, bcmath, mbstring, openssl, tokenizer, xml, ctype, json, curl
- MySQL 8.0 yoki MariaDB 10.5+
- mod_rewrite yoqilgan Apache/LiteSpeed

## 2. O'rnatish Bosqichlari
1. Ushbu arxiv tarkibini hostingdagi domeningiz ildiz papkasiga (`public_html` yoki shaxsiy vhost papkasi) yuklang va oching (unzip).
2. `.env.example` faylidan nusxa olib `.env` yarating:
   - `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` qiymatlarini hostingdagi MySQL bazangizga moslang.
   - `APP_URL=https://sizning-saytingiz.uz`
   - `INSTALL_TOKEN=ixtiyoriy_murakkab_xavfsizlik_tokeni`
3. Brauzer orqali `https://sizning-saytingiz.uz/install` sahifasiga o'ting va o'rnatish ustasini bajaring.
   Yoki SSH terminal orqali:
   ```bash
   cd backend
   php artisan app:install --admin-email=admin@market.uz --admin-password=MurakkabParol123! --seed-demo
   ```
4. O'rnatish yakunlangach `storage/installed.lock` fayli avtomatik hosil bo'ladi va qayta o'rnatish bloklanadi.

## 3. Cron Sozlamasi (Shared Hosting)
Hosting boshqaruv panelidagi Cron bo'limiga har daqiqalik quyidagi vazifani qo'shing:
```bash
* * * * * cd /path/to/backend && php artisan schedule:run >> /dev/null 2>&1
```
'@
Set-Content -Path (Join-Path $StagingDir "README_DEPLOY.md") -Value $DeployReadme -Encoding UTF8

# 8. Compress staging folder into zip using native Windows tar
Write-Host ">>> Compressing package into $ZipTarget..." -ForegroundColor Yellow
if (Get-Command tar.exe -ErrorAction SilentlyContinue) {
    & tar.exe -a -c -f $ZipTarget -C $StagingDir .
} else {
    Compress-Archive -Path "$StagingDir\*" -DestinationPath $ZipTarget -Force
}

# 9. Verify package contents
$ZipSize = (Get-Item $ZipTarget).Length / 1MB
Write-Host (">> Package generated successfully! Size: {0:N2} MB" -f $ZipSize) -ForegroundColor Green
Write-Host ">> File: $ZipTarget" -ForegroundColor Green

# Clean staging dir
Remove-Item -Recurse -Force $StagingDir
