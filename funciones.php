<?php
date_default_timezone_set("America/Santiago");
$hoy         = date("Y-m-d H:i:s");
$hoy_formateado  = date("YmdHis");
$hoy_corto  = date("Y-m-d");
$iva      = 1.19;
$PORC_MINIMO = 0.8;
$dia      = date('w');
$sistema   = "Sistema Judicial";
$findemes = date("Y-m-t", strtotime($hoy_corto));
// echo $findemes;exit;
function redondear_a_10($valor)
{

   // Convertimos $valor a entero
   $valor = intval($valor);

   // Redondeamos al m�ltiplo de 10 m�s cercano
   $n = round($valor, -1);

   // Si el resultado $n es menor, quiere decir que redondeo hacia abajo
   // por lo tanto sumamos 10. Si no, lo devolvemos as�.
   return $n < $valor ? $n + 10 : $n;
}

// Función para validar RUT sin contar puntos, guiones ni espacios
function validar_rut($rut)
{
   // Eliminar puntos, guiones y espacios
   $rut = preg_replace('/[\s\.-]/', '', $rut);

   // Verificar que tenga 7 u 8 dígitos y contener solo números
   if (!preg_match('/^\d{7,8}$/', $rut)) {
      return false; // Formato incorrecto
   }
   return true; // Formato correcto
}


function contar_valores($gru, $buscado)
{
   $cant = $_SESSION["cantidad"];
   if (!is_array($gru)) return NULL;
   $i = 0;
   $j = 0;
   foreach ($gru as $v) {

      if ($buscado == $v) {
         //  echo 'V : '.$v.' buscado :'.$buscado.'<br>';
         $i = $i + $cant[$j];
      };
      $j++;
   };
   //echo 'I : '.$i.'<br>';

   return $i;
};

function convierte_fecha($fecha)
{
   $fec = substr($fecha, 8, 2) . '/' . substr($fecha, 5, 2) . '/' . substr($fecha, 0, 4);
   return $fec;
};
function convierte_fecha_full($fecha)
{
   $fec = substr($fecha, 8, 2) . '/' . substr($fecha, 5, 2) . '/' . substr($fecha, 0, 4) . ' ' . substr($fecha, 11, 5);
   return $fec;
};

function convierte_fecha_SQL($fecha)
{
   $fec = substr($fecha, 6, 4) . '-' . substr($fecha, 3, 2) . '-' . substr($fecha, 0, 2);
   return $fec;
};

function convierte_fecha_SQL2($fecha)
{
   $fec = substr($fecha, 0, 4) . '-' . substr($fecha, 5, 2) . '-' . substr($fecha, 8, 2);
   return $fec;
};


//M�todo con str_shuffle() 
function generateRandomString($length = 10)
{
   return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}

function my_number_format($number, $dec_point, $thousands_sep)
{
   $was_neg = $number < 0; // Because +0 == -0
   $number = abs($number);

   $tmp = explode('.', $number);
   $out = number_format($tmp[0], 0, $dec_point, $thousands_sep);
   if (isset($tmp[1])) $out .= $dec_point . $tmp[1];

   if ($was_neg) $out = "-$out";

   return $out;
}

function write_log($modulo)
{
   global $hoy, $conn;
   $hoy = getdate();
   // print_r($hoy);exit;
   if ($_SESSION["id_usuario"] != 1) {
      $tsql        = "INSERT INTO BASE_REPORTES_OP.dbo.TBL_SIS_LOG
						(FECHA, ID_USUARIO, MODULO,SISTEMA) VALUES(GETDATE(), " . $_SESSION["id_usuario"] . ", '$modulo','INDICADORES');";
      // echo $tsql;exit; 
      $getDias    = $conn->prepare($tsql);
      $getDias->execute();
   };
}

function noCache()
{
   header("Expires: 0");
   header("Cache-Control: no-store, no-cache, must-revalidate");
   header("Cache-Control: post-check=0, pre-check=0", false);
   header("Pragma: no-cache");
}

function validar_clave($clave)
{
   global $error_clave;
   if (strlen($clave) < 6) {
      $error_clave = "La clave debe tener al menos 6 caracteres";
      return false;
   }
   if (strlen($clave) > 16) {
      $error_clave = "La clave no puede tener m�s de 16 caracteres";
      return false;
   }
   if (!preg_match('`[a-z]`', $clave)) {
      $error_clave = "La clave debe tener al menos una letra minuscula";
      return false;
   }
   if (!preg_match('`[A-Z]`', $clave)) {
      $error_clave = "La clave debe tener al menos una letra mayuscula";
      return false;
   }
   if (!preg_match('`[0-9]`', $clave)) {
      $error_clave = "La clave debe tener al menos un caracter numerico";
      return false;
   }
   $error_clave = "";
   return true;
}
