# ============================================================
# SSV Trucking System — Local Database Export Script
# Run this whenever you change the database schema BEFORE
# doing a git push so the SQL file stays up to date.
# Usage: Right-click > Run with PowerShell
#        OR run in terminal: .\deploy\export-db.ps1
# ============================================================

$mysqldumpPath = "C:\xampp\mysql\bin\mysqldump.exe"
$outputFile    = "$PSScriptRoot\..\ssv_trucking.sql"
$dbName        = "ssv_trucking"
$dbUser        = "root"
$dbPass        = ""

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  SSV Trucking — Database Exporter" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan

# Check mysqldump exists
if (-Not (Test-Path $mysqldumpPath)) {
    Write-Host "ERROR: mysqldump not found at $mysqldumpPath" -ForegroundColor Red
    Write-Host "Make sure XAMPP is installed correctly." -ForegroundColor Red
    Pause
    exit 1
}

Write-Host "`nExporting database '$dbName'..." -ForegroundColor Yellow

try {
    if ($dbPass -eq "") {
        & $mysqldumpPath -u $dbUser --no-tablespaces $dbName | Set-Content -Path $outputFile -Encoding UTF8
    } else {
        & $mysqldumpPath -u $dbUser -p"$dbPass" --no-tablespaces $dbName | Set-Content -Path $outputFile -Encoding UTF8
    }

    if ($LASTEXITCODE -eq 0) {
        $fileSize = [math]::Round((Get-Item $outputFile).Length / 1KB, 2)
        Write-Host "SUCCESS! Database exported to:" -ForegroundColor Green
        Write-Host "  $outputFile ($fileSize KB)" -ForegroundColor Green
        Write-Host "`nNext step: commit and push to GitHub to trigger auto-deploy." -ForegroundColor Cyan
    } else {
        Write-Host "ERROR: mysqldump failed with exit code $LASTEXITCODE" -ForegroundColor Red
    }
} catch {
    Write-Host "ERROR: $_" -ForegroundColor Red
}

Pause
