$content = Get-Content "php\database\dbs.sql"
$defs = @{}
$currentTable = ""
foreach ($line in $content) {
    if ($line -match 'CREATE TABLE `([^`]+)`') {
        $currentTable = $Matches[1]
        if (-not $defs.ContainsKey($currentTable)) {
            $defs[$currentTable] = @{}
        }
    }
    if ($line -match '(PRIMARY KEY|UNIQUE KEY|KEY) `([^`]+)`') {
        $keyType = $Matches[1]
        $keyName = $Matches[2]
        $keyId = "$keyType|$keyName"
        if ($defs[$currentTable].ContainsKey($keyId)) {
            Write-Output "DUPLICATE in table $currentTable : $keyType`:$keyName"
        } else {
            $defs[$currentTable][$keyId] = $line
        }
    }
}
Write-Output "Done"
