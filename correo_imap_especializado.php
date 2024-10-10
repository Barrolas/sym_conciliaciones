<!DOCTYPE html>
<html lang="en">

    <head>
        <meta http-equiv="refresh" content="30">
    </head>
</html>	

<?php
// ini_set('display_errors','On');
// Librerias
include('funciones.php');
require_once ('phpexcel2/vendor/autoload.php');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
require_once('PHPDecryptXLSXWithPassword.php');

// FIN LIBRERIAS
// SET UP CONEXION SQL SERVER / HADES
$serverName = "192.168.1.41\DESARROLLO"; 
$connectionInfo = array( "Database"=>"SYM", "UID"=>"sa", "PWD"=>"Admin1390+");
$conn           = sqlsrv_connect( $serverName, $connectionInfo);

// CONEXION a ZEUS
// SET UP CONEXION SQL SERVER

$serverName2 = "192.168.1.6\PRODUCCION"; 
$connectionInfo2 = array( "Database"=>"SYM", "UID"=>"asistenteti", "PWD"=>"Sym1390+");
$conn2           = sqlsrv_connect( $serverName2, $connectionInfo2);

// CONEXION A CHERKHAN

$serverName3 = "192.168.101.15\RECOVER"; 
$connectionInfo3 = array( "Database"=>"SYM", "UID"=>"sa", "PWD"=>"Hendrix1966.");
$conn3           = sqlsrv_connect( $serverName3, $connectionInfo3);

$serverName4 = "192.168.101.6\INFORMES"; 
$connectionInfo4 = array( "Database"=>"SYM", "UID"=>"sa", "PWD"=>"symserver");
$conn4           = sqlsrv_connect( $serverName4, $connectionInfo4);


$repositorio ="\\\\192.168.101.15";
$repositorio2 ="\\\\192.168.1.41";
// echo $folder = "$repositorio\\excel_pagos\ABAKOS";

// SET UP CUENTA DE CORREO
$hostname = '{mail.sepulvedaymora.cl}';
$username = 'pagosistemas@sepulvedaymora.cl';
$password = 'Symps1390+';

//if($mensaje->parts[1]->ifdisposition = "1" and $mensaje->parts[1]->disposition = "attachment"){Hacer lo de attachments}
$imap =imap_open($hostname,$username,$password,OP_READONLY) or die('Ha fallado la conexión: ' . imap_last_error());
$ref = '{mail.sepulvedaymora.cl}';

/*
-- LISTA DE BUZONES.

$list = imap_list($imap, "{imap.example.org}", "*");
if (is_array($list)) {
    foreach ($list as $val) {
        echo imap_utf7_decode($val) . "\n";
    }
} else {
    echo "imap_list failed: " . imap_last_error() . "\n";
}
exit;
*/

date_default_timezone_set('America/Buenos_Aires');
echo date_default_timezone_get();
$fecha= strtoupper(date("d F Y"));  //desde que fecha sincronizara
echo "SINCE : ".$fecha."<BR>";
// $fecha = '23 MARCH 2024';
// echo $fecha;exit;
//con la instrucción SINCE mas la fecha entre apostrofes ('')
//indicamos que deseamos los mails desde una fecha en especifico
//imap_search sirve para realizar un filtrado de los mails.
echo date('Y-m-d h:m:s').'<BR>';
echo $fecha.'<BR>';
$emails = imap_search($imap, 'SINCE "'.$fecha.'"');
// $emails = imap_search($imap, 'SINCE "18 JANUARY 2024"');
if (!$emails) {
	echo 'CASILLA VACIA';
	exit;
};

/*
foreach ($emails as $email) {
	$resultados = imap_fetch_overview($imap, $email, 0);
	 print_r($resultados[0]->subject);
	echo '<BR>';
	$pos 		= strpos($resultados[0]->subject, 'eguimiento Cartera Vigente al');
	if ($pos != '') {
		$proceso =  "RIPLEY";
		echo $proceso;
		exit;
		$tipo_archivo = 0;		
	}
}
exit;
*/
$indice = 0;

foreach ($emails as $email) {
	// print_r($emails);exit;
	// PROCESA TODOS LOS EMAIL DEL DIA
	//el ultimo de los coinciden
	/*
	
	for ($index = 0; $index < count($emails); $index++) {
		$indice = $emails[$index];
		
		// echo $r[0]->msgno.' - '.$r[0]->from.'<BR>';
		$pos = strpos($r[0]->from, 'Eduardo');	
		if ($pos != '') {		
			$ind = $emails[$index];
		}
	};
	*/
	$r = imap_fetch_overview($imap, $email, 0);
	//	print_r($email);echo "<BR>";
    echo "ESTE ES EL FROM : " . $r[0]->from.'<BR>';
	// echo "ELBODY : ".imap_fetchbody($imap, $email, 1);
    

	// echo "<BR>";
	
	$resultados = imap_fetch_overview($imap, $email, 0);
	
	// print_r($resultados);exit;
	$msgno = $resultados[0]->msgno;
	// echo "Numero de Mensaje Procesado : ".$msgno.'<BR>';
  
    echo "NUMERO MENSAJE : ".$msgno;
    echo  "<BR>SUBJECT : " . ($resultados[0]->subject).'<BR>';
   /*   if ($msgno <> 289) {
        continue;
    }
	 */
    //echo "SIGUE";
	// Valdacion si ya procese el mensaje
	$sql = 'Exec _SP_DV_CONSULTA_EMAIL_PROCESADOS '.$msgno;
	echo $sql.'<BR>';
	$stmt = sqlsrv_query( $conn4, $sql );

	if( $stmt === false) {			
		die( print_r( sqlsrv_errors(), true) );
	};

	$row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_NUMERIC);
	
	if (isset($row[0])) {
		echo "<BR>Encontro Que ya se proceso<BR>";
		echo "-------------------------------------------------------------------------------------<br>";
		continue;
	}
	sqlsrv_free_stmt( $stmt);
	// exit;

//	echo $resultados[0]->msgno."<BR>";exit;
	// continue;
	//echo "<BR><BR>";
	$tipo_archivo = 0;
	$proceso = '';
	// $pos 		= strpos($resultados[0]->from, 'Eduardo');
	$pos 		= strpos($resultados[0]->from, 'Zenteno');
	
	// echo $resultados[0]->subject."<BR>";
   
	// echo $pos.'<BR>';
	if ($pos != '') {
		
		$pos 		= strpos(imap_fetchbody($imap, $email, 1), 'pagos');
		// "EL POS ZENTENO : ".$pos;exit;
		// echo "POS : ".$pos.'<BR>';	exit;
		if ($pos != '') {		
			$tipo_archivo = 1;
            $proceso =  "ABAKOS";
		} else {
			$pos 		= strpos(imap_fetchbody($imap, $email, 1), 'PISAN LAS ANTERIORES');
			// "EL POS ZENTENO : ".$pos;exit;
			// echo "POS : ".$pos.'<BR>';	exit;
			if ($pos != '') {		
				$tipo_archivo = 2;
			} else {
				continue;
			};
			$proceso =  "ABAKOS_CARGAS";
			echo "ELPROCESO = ".$proceso;
			$tipo_archivo = 2;
            
		};
		
		echo "ELPROCESO = ".$proceso;
		
	}

    $pos 		= strpos($resultados[0]->subject, 'TERO CD');
	if ($pos != '') {
		$proceso =  "RUTERO";
		echo "PROCESS RUTERO";
		echo $proceso;
		$tipo_archivo = 0;			
	}

	$pos 		= strpos($resultados[0]->subject, 'Emerix Recover');
	if ($pos != '') {
		$proceso =  "CRUZVERDE PILOTO";
		echo $proceso;
		$tipo_archivo = 0;
			
	}
	$pos 		= strpos($resultados[0]->subject, 'Interfaces Empresas');
	if ($pos != '') {
		$proceso =  "CRUZVERDE VIGENTE";
		$tipo_archivo = 0;
		echo $proceso;
	};
	/*echo "SUBJECT";
	print($resultados[0]->subject);*/
	$pos 		= strpos($resultados[0]->subject, 'de pago Empresas CASTIGO');
	// echo $pos;
	if ($pos != '') {
		$proceso =  "CRUZVERDE CASTIGO";
		$tipo_archivo = 0;
		echo $proceso;
		// exit;
	};

	$pos 		= strpos(imap_fetchbody($imap, $email, 1), 'con saludar, adjunto archivo de pagos');
	echo 'con saludar, adjunto archivo de pagos'.$pos;
	if ($pos != '') {
		$proceso =  "RIPLEY";
		echo $proceso;		
		$tipo_archivo = 0;	
			
	}

	$pos 		= strpos($resultados[0]->subject, 'Recupero Castigo Banco RECOVER');
	if ($pos != '') {
		$proceso =  "FALABELLA";
		echo $proceso;
		
		$tipo_archivo = 0;		
	}

    $pos 		= strpos(imap_fetchbody($imap, $email, 1), 'tarjetaprivilege.cl');
	// echo "EL POS : ".$pos.'<BR>';exit;
	if ($pos != '') {
		$proceso =  "PRIVILEGE";
		echo $proceso;
		$tipo_archivo = 0;	
	}

	$pos 		= strpos($resultados[0]->subject, 'DIARIOS RIPLEY');
	echo "EL POS RIPLEY ::::::: ".$pos.'<BR>';
	if ($pos != '') {
		$proceso =  "RIPLEY_VIGENTES";
		echo $proceso;
		$tipo_archivo = 0;	
	}
	
	$pos 		= strpos($resultados[0]->subject, 'cover Consumo Vgte');
	echo "EL POS RECOVER CONSUMO ::::::: ".$pos.'<BR>';
	if ($pos != '') {
		$proceso =  "FALABELLA_CONSUMO";
		echo $proceso;
		$tipo_archivo = 0;	
	}
	
	$pos 		= strpos($resultados[0]->subject, 'agos recover al');
	echo "EL POS OPERACIONES RECOVER ::::::: ".$pos.'<BR>';
	if ($pos != '') {
		$proceso =  "COOPEUCH_EXCLUSIONES";
		echo $proceso;
		$tipo_archivo = 0;	
	}

	$pos 		= strpos($resultados[0]->subject, 'AGOS HIPOTECARIO');
	echo "EL POS PAGOS HIPOTECARIO ::::::: ".$pos.'<BR>';
	if ($pos != '') {
		$proceso =  "BANCO_HIPO";
		echo $proceso;
		$tipo_archivo = 0;	
	}
	
    echo "PPPPPPPPPROCESO : " . $proceso;
	// continue;
	
	/*
	// echo imap_fetchbody($imap, $email, 1);
	$pos 		= strpos(imap_fetchbody($imap, $email, 1), 'pagos');
	// echo "POS : ".$pos.'<BR>';	exit;
	if ($pos != '') {		
		$tipo_archivo = 1;
	} else {
		$tipo_archivo = 0;
	};
	*/
	// echo $tipo_archivo.'<BR>';
	// exit;

	// print_r("PASO!!!");
	foreach ($resultados as $detalles) {
		// print_r($detalles);

		$structure 	= imap_fetchstructure($imap, $detalles->msgno);	
		// print_r($structure);
		$attachments = array();
		
		if(isset($structure->parts) && count($structure->parts)) {

			for($i = 0; $i < count($structure->parts); $i++) {

				$attachments[$i] = array(
					'is_attachment' => false,
					'filename' => '',
					'name' => '',
					'attachment' => ''
				);
				
				if($structure->parts[$i]->ifdparameters) {
					foreach($structure->parts[$i]->dparameters as $object) {
						if(strtolower($object->attribute) == 'filename') {
							$attachments[$i]['is_attachment'] = true;
							$attachments[$i]['filename'] = $object->value;
						}
					}
				}

				if($structure->parts[$i]->ifparameters) {
					foreach($structure->parts[$i]->parameters as $object) {
						if(strtolower($object->attribute) == 'name') {
							$attachments[$i]['is_attachment'] = true;
							$attachments[$i]['name'] = $object->value;
						}
					}
				}

				if($attachments[$i]['is_attachment']) {
					$attachments[$i]['attachment'] = imap_fetchbody($imap, $detalles->msgno, $i+1);
					if($structure->parts[$i]->encoding == 3) { // 3 = BASE64
						$attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
					}
					elseif($structure->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
						$attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
					}
				}
				
			};
			
			/* ITERA ATTACHMENT PARA GUARDARLOS EN COMPARTIDO */
            $dia		= date('d');
            $mes 		= date('m');
            $ano 		= date('Y');
			foreach($attachments as $attachment)
			{
				if($attachment['is_attachment'] == 1)
				{
					$filename = $attachment['name'];
					if(empty($filename)) $filename = $attachment['filename'];
					if(empty($filename)) $filename = time() . ".xlsx";
					
					// REPOSITORIO ASIGNADO
					
					if ($proceso == 'FALABELLA_CONSUMO') {
						$folder = "$repositorio\\excel_pagos\FALABELLA";
					}

					if ($proceso == 'ABAKOS') {
						$folder = "$repositorio\\excel_pagos\ABAKOS";
					}

                    if ($proceso == 'RUTERO') {
						$folder = "$repositorio\\excel_pagos\RUTERO";
					}
                    
                    if ($proceso == 'ABAKOS_CARGAS') {
						$folder = "$repositorio\\archivos_cargas\abakos";
					}

					if ($proceso == 'PRIVILEGE') {
						$folder = "$repositorio\\excel_pagos\PRIVILEGE";
					}					
					elseif ($proceso == 'COOPEUCH CASTIGO') {
						$folder = "$repositorio\\excel_pagos\COOPEUCH";
						
					}
					elseif ($proceso == 'FALABELLA') {
						$folder = "$repositorio\\excel_pagos\FALABELLA";
					}
					elseif ($proceso == 'CRUZVERDE VIGENTE' or $proceso == 'CRUZVERDE CASTIGO' or $proceso == 'CRUZVERDE PILOTO') {
						$folder = "$repositorio\\excel_pagos\CRUZVERDE";
					} elseif ($proceso == 'RIPLEY') {
						$folder = "$repositorio\\excel_pagos\RIPLEY";						
					} elseif ($proceso == 'RIPLEY_VIGNETES') {
						$folder = "$repositorio\\excel_pagos\RIPLEY";						
					};

					echo "EL TIPO ARCHIVO :".$tipo_archivo."<BR>";
					echo "EL PROCESO :".$proceso."<BR>";
					
					if ($proceso == 'ABAKOS' and $tipo_archivo == 1) {
						echo "".$tipo_archivo;
						echo "<br>CARGA ABAKOS<br>";	
						echo $folder ."\\$filename";
						/*
						if (!fopen($folder . "\\pagos.xlsx", "r+")) {
							die("archivo con problemas");
						}
						$fp = fopen($folder . "\\pagos.xlsx", "r+");
						
						*/

						$fp = fopen($folder ."\\$filename", "w+");
						fwrite($fp, $attachment['attachment']);
						fclose($fp);
                        
						copy($folder ."\\$filename",$folder ."\\pagos.xlsx");
						//echo $folder ."\\$filename";exit;
						unlink($folder ."\\$filename");
						/*
						$sql = 'Exec _SP_PROCESO_PAGOS 76937405;';
						echo '<br>'.$sql.'<br>';
						$stmt = sqlsrv_query( $conn, $sql );
						*/
						
					} else {
						// CARGAS
                        if ($proceso == 'RIPLEY_VIGENTES' ) {
							$stmt = sqlsrv_query( $conn, "delete from _dv_vigentes_diarios_ripley" );
							echo "RIPLEY _ VIGENTES".$proceso;
							$fp = fopen("vigentes.csv", "w+");
							fwrite($fp, $attachment['attachment']);
							fclose($fp);	
							/*
							$folder = "$repositorio\\excel_pagos\RIPLEY\\";
							echo  $folder ."VIGCAR_EMPEX_RECOVER_DV.csv"			;
							copy("vigentes.csv",$folder ."VIGCAR_EMPEX_RECOVER.csv");
                            //unlink('vigentes.csv');
							$stmt = sqlsrv_query( $conn, "delete from _dv_vigentes_diarios_ripley" );
							*/
							$fp = fopen ("vigentes.csv","r");
							// echo date('d-m-Y h:m:s');
							$data = fgetcsv ($fp, 5000, ";");
							while ($data = fgetcsv ($fp, 5000, ";")) {
							$num = count ($data);    
									/*print "Num : ".$num."<BR>";
									echo $data[0].' -> '.$data[1].' -> '.$data[2].' -> '.$data[3];
									echo "<BR>";
									*/
									$sql = " INSERT INTO [dbo].[_dv_vigentes_diarios_ripley]
									(
									[FECHA_PROC]									
									,[RUT_CLIENTE]
									)
							VALUES
							(".trim($data[0])."
									,'".trim($data[7])."'
									,'".trim($data[12])."'
									)"; 
									//echo $sql.'<BR>';
								$stmt = sqlsrv_query( $conn2, $sql );
							};
							$sql = 'Exec _SP_DV_GUARDA_EMAIL_PROCESADOS 777777,'.$msgno;
							echo $sql;
							$stmt = sqlsrv_query( $conn4, $sql );
							unlink('vigentes.csv');
						}						 
						elseif ($proceso == 'COOPEUCH_EXCLUSIONES' ) {
							$stmt = sqlsrv_query( $conn4, "delete from dv_exclusion_coopeuch_terreno_telefonica" );
							echo $proceso;
							$proc = 'EXCLUSIONOPERACIONES_TELEFONICA_TERRENO_'.$ano.$mes.$dia.'.csv'; 
							$fp = fopen($proc, "w+");
							fwrite($fp, $attachment['attachment']);
							fclose($fp);
							
							echo $proceso;
							$proc1 = 'EXCLUSIONOPERACIONES_DIGITAL_'.$ano.$mes.$dia.'.csv'; 
							$fp = fopen($proc1, "w+");
							fwrite($fp, $attachment['attachment']);
							fclose($fp);
                            $fp = fopen ($proc,"r");
							// echo date('d-m-Y h:m:s');
							while ($data = fgetcsv ($fp, 1000, ",")) {
							/*  $num = count ($data);
								
							
									print "Num : ".$num."<BR>";
									echo $data[0].' -> '.$data[1].' -> '.$data[2].' -> '.$data[3];
									echo "<BR>";
							*/
									$sql = " INSERT INTO [dbo].[dv_exclusion_coopeuch_terreno_telefonica]
									([PERIODO_ACTUAL]
									,[RUT]
									,[DV]
									,[NUM_OPERACION]
									,[FECHA_HORA_ULTIMA_GESTION]
									,[ACCION_ULTIMA_GESTION]
									,[CONTACTO_ULTIMA_GESTION]
									,[RESPUESTA_ULTIMA_GESTION]
									,[GLOSA_ULTIMA_GESTION]
									,FECHA_PROCESO)
							VALUES
									(".trim($data[0])."
									,".trim($data[1])."
									,".trim($data[2])."
									,".trim($data[3])."
									,".trim($data[4])."
									,".trim($data[5])."
									,".trim($data[6])."
									,".trim($data[7])."
									,".trim($data[8])."
									,".trim($data[9])."
									,'$hoy_corto')";
							//        echo $sql.'<BR>';
								$stmt = sqlsrv_query( $conn2, $sql );

							}					
							copy("rutero.csv",$folder ."\\procesados\\rutero$hoy_corto.csv");
                            unlink('rutero.csv');
						} 
						elseif ($proceso == 'RUTERO' ) {

							echo $proceso;
							$fp = fopen("rutero.csv", "w+");
							fwrite($fp, $attachment['attachment']);
							fclose($fp);

                            $fp = fopen ("rutero.csv","r");
							// echo date('d-m-Y h:m:s');
							while ($data = fgetcsv ($fp, 1000, ",")) {
							/*  $num = count ($data);
								
							
									print "Num : ".$num."<BR>";
									echo $data[0].' -> '.$data[1].' -> '.$data[2].' -> '.$data[3];
									echo "<BR>";
							*/
									$sql = " INSERT INTO [dbo].[_dv_rutero]
									([RUTDEUDOR]
									,[OPERACION]
									,[FECHAGESTION]
									,[ESTADO]
									,[FECHAPROCESO])
							VALUES
									(".trim($data[0])."
									,".trim($data[1])."
									,".trim($data[2])."
									,".trim($data[3])."
									,'$hoy_corto')";
							//        echo $sql.'<BR>';
								$stmt = sqlsrv_query( $conn2, $sql );

							}					
							copy("rutero.csv",$folder ."\\procesados\\rutero$hoy_corto.csv");
                            unlink('rutero.csv');
						} 
						elseif ($proceso == 'CRUZVERDE CASTIGO' ) {

							$fp = fopen("$filename", "w");
							fwrite($fp, $attachment['attachment']);
							fclose($fp);

							$zip        = new ZipArchive;
							$path_rar   = 'Interface_cast_940.zip';

							// devuelve "C:\.../Data_Normalizacion_202109_20210906_out.rar"

							/* Comparamos exactitud con "true" */
							if ($zip->open($path_rar) === true) {
								$zip->extractTo('.');
								$zip->close();
								echo ("archivo descomprimido!");
							} else {
								echo ("Ha ocurrido un error al descomprimir!");
							} ;

							$folder = "$repositorio\\excel_pagos\CRUZVERDE\\";
							copy('01_pagos_940.xls', $folder.'01_pagos_940.csv');
						// FALABELLA_CONSUMO
						} elseif ($proceso == 'FALABELLA_CONSUMO' ) { 
							/*
							$fp = fopen("$filename", "w");
							fwrite($fp, $attachment['attachment']);
							fclose($fp);
							
							$reader         = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
							$spreadsheet    = $reader->load($filename);
							$sheet          = $spreadsheet->getActiveSheet();

							$spreadsheet->getActiveSheet()->setTitle("Hoja1");
							$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");

							$writer->save("Pagos.xlsx");
							
							copy('Pagos.xlsx', 'c:\excel_pagos\FALABELLA\\falabella_consumo.xlsx');

							unlink($filename);
							unlink('Pagos.xlsx');

							$sql  = 'Exec _SP_CARGA_FALABELLA_CONSUMO;';
							$stmt = sqlsrv_query( $conn3, $sql );
							*/
						} elseif ($proceso == 'BANCO_HIPO' ) { 
							
							$fp = fopen("$filename", "w");
							fwrite($fp, $attachment['attachment']);
							fclose($fp);
							
							$reader         = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
							$spreadsheet    = $reader->load($filename);
							$sheet          = $spreadsheet->getActiveSheet();

							$spreadsheet->getActiveSheet()->setTitle("Hoja1");
							$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");

							$writer->save("Pagos.xlsx");
							
							copy('Pagos.xlsx', 'c:\excel_pagos\PagosRecover.xlsx');

							unlink($filename);
							unlink('Pagos.xlsx');

							$sql  = 'Exec _SP_CARGA_FALABELLA_HIPO;';
							$stmt = sqlsrv_query( $conn3, $sql );	

							$sql  = 'Exec _SP_COPIA_HIPO;';
							$stmt = sqlsrv_query( $conn2, $sql );

						} elseif ($proceso == 'ABAKOS_CARGAS' ) {

							echo 'ATACHADO : ' . $filename.'<BR>';
                            $pos 		= strpos($filename, 'IPL');
                            if ($pos != '') {
                                $fp = fopen("$filename", "w");
                                fwrite($fp, $attachment['attachment']);
                                fclose($fp);
                                
                                $reader         = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                                $spreadsheet    = $reader->load($filename);
                                $sheet          = $spreadsheet->getActiveSheet();

                                $spreadsheet->getActiveSheet()->setTitle("Hoja1");
                                $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");

                                $writer->save("Pagos.xlsx");
                                
                                copy('Pagos.xlsx', 'c:\archivos_cargas\abakos\\BASEIPL.xlsx');

                                unlink($filename);
                                unlink('Pagos.xlsx');
                            }

                            $pos 		= strpos($filename, 'SPL');
                            if ($pos != '') {
                                $fp = fopen("$filename", "w");
                                fwrite($fp, $attachment['attachment']);
                                fclose($fp);
                                $reader         = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                                $spreadsheet    = $reader->load($filename);
                                $sheet          = $spreadsheet->getActiveSheet();

                                $spreadsheet->getActiveSheet()->setTitle("Hoja1");
                                $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");

                                $writer->save("Pagos.xlsx");
                                
                                copy('Pagos.xlsx', 'c:\archivos_cargas\abakos\\BASESPL.xlsx');

                                unlink($filename);
                                unlink('Pagos.xlsx');
                            }

                            $pos 		= strpos($filename, 'PP');
                            if ($pos != '') {
                                $fp = fopen("$filename", "w");
                                fwrite($fp, $attachment['attachment']);
                                fclose($fp);

                                $reader         = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                                $spreadsheet    = $reader->load($filename);
                                $sheet          = $spreadsheet->getActiveSheet();

                                $spreadsheet->getActiveSheet()->setTitle("Hoja1");
                                $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");

                                $writer->save("Pagos.xlsx");
                                
                                copy('Pagos.xlsx', 'c:\archivos_cargas\abakos\\BASEPP.xlsx');

                                unlink($filename);
                                unlink('Pagos.xlsx');
                            }
							
							// copy('asignacion_tc_90002.xls', $folder.'asignacion_tc_90002.xlsx');
							
						} elseif ($proceso == 'CRUZVERDE VIGENTE' ) {

							$fp = fopen("$filename", "w");
							fwrite($fp, $attachment['attachment']);
							fclose($fp);

                            echo "Archivo : " . $filename."<BR>";

							$zip        = new ZipArchive;
							$path_rar   = 'Interface_90002.zip';
							// devuelve "C:\.../Data_Normalizacion_202109_20210906_out.rar"

							/* Comparamos exactitud con "true" */
							if ($zip->open($path_rar) === true) {
								$zip->setPassword("INTER#Solv#2022");
								$zip->extractTo('.');
								$zip->close();
								echo ("archivo descomprimido!");
							} else {
								echo ("Ha ocurrido un error al descomprimir!");
							}

							$folder = "$repositorio\\excel_pagos\CRUZVERDE\\";
							
							copy('asignacion_tc_90002.xls', $folder.'asignacion_tc_90002.xlsx');
							
						} elseif ($proceso == 'CRUZVERDE PILOTO' ) {
							echo $proceso;
							$fp = fopen("$filename", "w+");
							fwrite($fp, $attachment['attachment']);
							fclose($fp);

							$zip        = new ZipArchive;
							
							$path_rar   = 'RECOVER_'.$dia.'_'.$mes.'.zip';
							
							$archivo    = 'RECOVER_'.$dia.'_'.$mes.'.xlsx';
							echo "ARCHIVO : ".$path_rar.'<BR>';
							if ($zip->open($path_rar) === true) {
								$zip->setPassword("INTER#Solv#2022");
								$zip->extractTo('.');
								$zip->close();
								echo ("<BR>archivo descomprimido!RECOVER'.$dia.'_'.$mes.'.zip<BR>");
							} else {
								echo ("Ha ocurrido un error al descomprimir!");
							}

							$folder = "$repositorio\\excel_pagos\CRUZVERDE\\";
							copy($archivo, $folder.'pagos_piloto.xlsx');							
						} elseif ($proceso == 'RIPLEY') {
							echo $proceso;
							$fp = fopen("$filename", "w+");
							fwrite($fp, $attachment['attachment']);
							fclose($fp);

							$spreadsheet = new Spreadsheet();
							$inputFileType = 'Xlsx';
							$inputFileName = 'RECOVER.xlsx';

							/*

							$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName );

							$spreadsheet->getActiveSheet()->setTitle("Pagos");

							$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");

							$writer->save("RECOVER.xlsx");
							*/
							copy("RECOVER.xlsx",$folder ."\\RECOVER.xlsx");

						} elseif ($proceso == 'PRIVILEGE') {
							echo $proceso . ' FILENAME : '.$filename ."<BR>";
							$ayer = date('Y-m-d', strtotime('-1 day'));
                            
                            echo "<BR>";
                            echo substr($ayer,5,2).'<BR>';

                            $dia1		= substr($ayer,5,2);
                            $mes1 		= substr($ayer,8,2);
                            echo 'Pagos del '.$mes1.'-'.$dia1.'.xlsx';
                            

                            $fp = fopen("$filename", "w+");
							fwrite($fp, $attachment['attachment']);
							fclose($fp);

							$path_rar = '';
                            $pos2 		= strpos($filename, 'agos del');
                            if ($pos2 != '') {
                                $dia--;
                                $path_rar   = 'Pagos del '.$mes1.'-'.$dia1.'.xlsx';;		
                            }; 

                            $pos2 		= strpos($filename, 'agos del Fin de Semana');
                            if ($pos2 != '') {
                                $path_rar   = 'Pagos del Fin de Semana.xlsx';		
                            };     
                            
                            if ($path_rar == '') {continue;}

                            $spreadsheet = new Spreadsheet();
                            $inputFileType = 'Xlsx';
                            $inputFileName = $path_rar;

                            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName );

                            $spreadsheet->getActiveSheet()->setTitle("Pagos");

                            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");

                            $writer->save("PagosPrivilege.xlsx");

							copy("PagosPrivilege.xlsx",$folder ."\\pagos.xlsx");
							unlink("PagosPrivilege.xlsx");
						} elseif ($proceso == 'FALABELLA') {
							echo $proceso;
							$fp = fopen("$filename", "w+");
							fwrite($fp, $attachment['attachment']);
							fclose($fp);

							$encryptedFilePath  = 'RECOVER.xlsx';
							$password           = '1ex1c0m';
							$decryptedFilePath  = 'RECOVER1.xlsx';

							decrypt($encryptedFilePath, $password, $decryptedFilePath);

							$reader         = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
							$spreadsheet    = $reader->load($decryptedFilePath);
							$sheet          = $spreadsheet->getActiveSheet();

							$spreadsheet->getActiveSheet()->setTitle("Pagos");
							$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");

							$writer->save("Pagos.xlsx");
							copy("RECOVER1.xlsx",$folder ."\\pagos.xlsx");
							unlink("RECOVER1.xlsx");
						}
					};	
				}
			}
			// print_r($attachments);
			//	echo "<BR>";
		}
		/*
				// PROCESAMIENTO DE PAGOS
				if ($proceso == 'ABAKOS') {
					$sql = 'Exec _SP_PROCESO_PAGOS 76937405;';
					echo '<br>'.$sql.'<br>';
					$stmt = sqlsrv_query( $conn, $sql );
				} elseif ($proceso == 'CRUZVERDE CASTIGO') {
					$sql = 'EXEC _SP_PROCESO_PAGOS 89807202; ';
					echo '<br>'.$sql.'<br>';
					$stmt = sqlsrv_query( $conn, $sql );
				} elseif ($proceso == 'CRUZVERDE VIGENTE') {
					$sql = 'EXEC _SP_PROCESO_PAGOS 89807200;'; //
					echo '<br>'.$sql.'<br>';
					$stmt = sqlsrv_query( $conn, $sql );
				};	
				// FIN PROCESO PAGOS
		*/
		$structure = imap_fetchstructure($imap, $detalles->msgno);
		//echo $proceso."<BR>";exit;

        if ($proceso == 'ABAKOS') {
			$sql = 'Exec _SP_DV_GUARDA_EMAIL_PROCESADOS 76937405,'.$msgno;
			echo $sql;
			$stmt = sqlsrv_query( $conn4, $sql );
			/*
			if( $stmt === false) {			
				die( print_r( sqlsrv_errors(), true) );
			}*/
			//  'FALABELLA_CONSUMO'
		}  elseif ($proceso ==  'FALABELLA_CONSUMO') {
			$sql = 'Exec _SP_DV_GUARDA_EMAIL_PROCESADOS 999999,'.$msgno;
			echo $sql;
			$stmt = sqlsrv_query( $conn4, $sql );
			/*
			if( $stmt === false) {			
				die( print_r( sqlsrv_errors(), true) );
			}*/	
		}  elseif ($proceso == 'ABAKOS_CARGAS') {
			$sql = 'Exec _SP_DV_GUARDA_EMAIL_PROCESADOS 89807202,'.$msgno;
			echo $sql;
			$stmt = sqlsrv_query( $conn4, $sql );
			/*
			if( $stmt === false) {			
				die( print_r( sqlsrv_errors(), true) );
			}*/

		} elseif ($proceso == 'CRUZVERDE CASTIGO') {
			$sql = 'Exec _SP_DV_GUARDA_EMAIL_PROCESADOS 89807202,'.$msgno;
			echo $sql;
			$stmt = sqlsrv_query( $conn4, $sql );
			/*
			if( $stmt === false) {			
				die( print_r( sqlsrv_errors(), true) );
			}*/
		} elseif ($proceso == 'CRUZVERDE VIGENTE') { // CALL;ROBOT;
			$sql = 'Exec _SP_DV_GUARDA_EMAIL_PROCESADOS 89807200,'.$msgno;
			echo $sql;
			$stmt = sqlsrv_query( $conn4, $sql );

			$sql = 'Exec _SP_DV_GUARDA_EMAIL_PROCESADOS 89807201,'.$msgno;
			echo $sql;
			$stmt = sqlsrv_query( $conn4, $sql );
			/*
			if( $stmt === false) {			
				die( print_r( sqlsrv_errors(), true) );
			}*/
			// PROCESO CRUZVERDE ROBOT Exec _SP_PROCESO_PAGOS 89807200;
			/*
			$sql = 'Exec _SP_PROCESO_PAGOS 89807201;';
			echo $sql;			
			$stmt = sqlsrv_query( $conn, $sql );
			*/
		} elseif ($proceso == 'CRUZVERDE PILOTO') { // PILOTO;
			$sql = 'Exec _SP_DV_GUARDA_EMAIL_PROCESADOS 89807204,'.$msgno;
			echo $sql;
			$stmt = sqlsrv_query( $conn4, $sql );
		}
			elseif ($proceso == 'RUTERO') { // PILOTO;
				$sql = 'Exec _SP_DV_GUARDA_EMAIL_PROCESADOS 1,'.$msgno;
				echo $sql;
				$stmt = sqlsrv_query( $conn4, $sql );	
		} elseif ($proceso == 'RIPLEY') {
			$sql = 'Exec _SP_DV_GUARDA_EMAIL_PROCESADOS 77360390,'.$msgno;
			echo $sql;
			$stmt = sqlsrv_query( $conn4, $sql );
        } elseif ($proceso == 'PRIVILEGE') {
			$sql = 'Exec _SP_DV_GUARDA_EMAIL_PROCESADOS 77026214,'.$msgno;
			echo $sql;
			$stmt = sqlsrv_query( $conn4, $sql );    
		} elseif ($proceso == 'FALABELLA') {
			$sql = 'Exec _SP_DV_GUARDA_EMAIL_PROCESADOS 96509669,'.$msgno; // PLAN PAGOS
			$stmt = sqlsrv_query( $conn4, $sql );
			$sql = 'Exec _SP_DV_GUARDA_EMAIL_PROCESADOS 96509661,'.$msgno; // BANCO FALABELLA (EXTRA-JUDICIAL)
			$stmt = sqlsrv_query( $conn4, $sql );
			$sql = 'Exec _SP_DV_GUARDA_EMAIL_PROCESADOS 96509671,'.$msgno; // BANCO FALABELLA VIGENTE ( REQUISICION )
			$stmt = sqlsrv_query( $conn4, $sql );
			echo $proceso;
		}
		echo "----------------------------------------------------------------------------";
	}
	
};	
imap_close($imap);

// exit;
echo "FINALIZA OK";
?>