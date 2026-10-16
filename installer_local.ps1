# À lancer UNE FOIS depuis le dossier du projet : C:\wamp64\www\mon-ecole
#   powershell -ExecutionPolicy Bypass -File installer_local.ps1
$ErrorActionPreference = 'Stop'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

$racine = Join-Path (Get-Location) 'public\vendor'
$dirs = @('jquery', 'datatables\js', 'datatables\css', 'datatables\images', 'fonts')
foreach ($d in $dirs) { New-Item -ItemType Directory -Force -Path (Join-Path $racine $d) | Out-Null }

function Telecharger($url, $dest) {
    Write-Host "-> $dest"
    Invoke-WebRequest -Uri $url -OutFile (Join-Path $racine $dest) -UseBasicParsing
}

# jQuery
Telecharger 'https://code.jquery.com/jquery-3.7.1.min.js' 'jquery\jquery-3.7.1.min.js'

# DataTables
$dt = 'https://cdn.datatables.net/1.13.8'
Telecharger "$dt/js/jquery.dataTables.min.js"   'datatables\js\jquery.dataTables.min.js'
Telecharger "$dt/css/jquery.dataTables.min.css" 'datatables\css\jquery.dataTables.min.css'
foreach ($img in 'sort_both','sort_asc','sort_desc','sort_asc_disabled','sort_desc_disabled') {
    Telecharger "$dt/images/$img.png" "datatables\images\$img.png"
}

# Polices (Inter 400/600, Lora 600) via fontsource
$fs = 'https://cdn.jsdelivr.net/npm/@fontsource'
Telecharger "$fs/inter/files/inter-latin-400-normal.woff2" 'fonts\inter-400.woff2'
Telecharger "$fs/inter/files/inter-latin-600-normal.woff2" 'fonts\inter-600.woff2'
Telecharger "$fs/lora/files/lora-latin-600-normal.woff2"   'fonts\lora-600.woff2'

# Feuille de style des polices
@"
@font-face { font-family: 'Inter'; font-weight: 400; font-style: normal; font-display: swap; src: url('inter-400.woff2') format('woff2'); }
@font-face { font-family: 'Inter'; font-weight: 600; font-style: normal; font-display: swap; src: url('inter-600.woff2') format('woff2'); }
@font-face { font-family: 'Lora';  font-weight: 600; font-style: normal; font-display: swap; src: url('lora-600.woff2') format('woff2'); }
"@ | Set-Content -Encoding UTF8 (Join-Path $racine 'fonts\fonts.css')

Write-Host "`nTerminé. Fichiers dans public\vendor" -ForegroundColor Green
