$sourcePath = "C:/xamp74/htdocs/conciliacion/TransferenciasRecibidas.xlsx"
$destinationPath = "\\192.168.1.193\Procesos_HV\Conciliaciones\Archivo_Transferencias\"

Copy-Item -Path $sourcePath -Destination $destinationPath