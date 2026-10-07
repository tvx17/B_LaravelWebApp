<#
.SYNOPSIS
Nimmt die Codex-Sitzung dieses Projekts im Terminal wieder auf.
.EXAMPLE
.\Resume-Codex.ps1
.EXAMPLE
.\Resume-Codex.ps1 -WhatIf
#>
[CmdletBinding(SupportsShouldProcess = $true)]
param(
    [ValidatePattern('^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$')]
    [string]$SessionId = '01a11494-ef9b-7f32-acbc-6dfe5d53b688'
)

$ErrorActionPreference = 'Stop'
$projectDirectory = $PSScriptRoot
$codexCommand = Get-Command codex -ErrorAction SilentlyContinue
if (-not $codexCommand) {
    throw 'Codex CLI wurde im PATH nicht gefunden. Installiere die CLI und melde dich mit codex login an.'
}

if ($PSCmdlet.ShouldProcess($projectDirectory, "Codex-Sitzung $SessionId wieder aufnehmen")) {
    Push-Location -LiteralPath $projectDirectory
    try {
        & $codexCommand resume $SessionId --cd $projectDirectory
        if ($LASTEXITCODE -ne 0) {
            throw "Codex wurde mit Exitcode $LASTEXITCODE beendet. Die gespeicherte Sitzung muss im lokalen Codex-Profil vorhanden sein."
        }
    }
    finally {
        Pop-Location
    }
}
