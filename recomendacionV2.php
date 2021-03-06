<?php

$tiempo_inicio = microtime_float();
header("Content-Type: text/html;charset=utf-8");
set_time_limit (3600);

include("conexion.php");
include("pearson.php");
include("calculoDistancia.php");
require_once("JSON.php");  

/*
//Variables resividas desde el usuario
$usuario = $_POST['usuario'];
$latitud = $_POST["latitud"];
$longitud = $_POST["longitud"];
$categoria = $_POST["categoria"];
$radioBusqueda = $_POST["radioBusqueda"];
*/
$latitud = "-33.4547699";
$longitud = "-70.64989889999998";
$categoria = 10;
$radioBusqueda = 10;
$usuario = "benjamin.g";

//Def Variables
$json = new Services_JSON;

$item_id = 0;
$usuario_id = 0;
$valor = 0;
$indice = 0;
$d = 0;

$datos=array();

$hoy = getdate();
$fecha = "1900-".$hoy["mon"]."-".$hoy["mday"];
$dia = $hoy ["weekday"];
$hora = $hoy ["hours"].":".$hoy["minutes"].":00.000000";

//Conexion BdD
$con=mysql_connect($host,$userDb,$passDb) or die ("problemas con servidor");
mysql_select_db($db,$con) or die("problemas con bd");

//Filtro por id_categoria, Espacio temporal (Fecha, dia y hora) y radio de busqueda
$filtro_categoria = mysql_query("SELECT itm_id FROM itm_categoria WHERE cat_id = '$categoria' ") or die (mysql_error());

while ($r_filtro_categoria= mysql_fetch_array($filtro_categoria))
  {
    $id_categoria = $r_filtro_categoria['itm_id'];
    //Lunes
    if($dia == "Monday")
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE itm_id ='$id_categoria' and hr_lun_ini < '$hora' and 
        hr_lun_cie > '$hora'") or die (mysql_error());
    }
    //Martes
    elseif($dia == "Tuesday")
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE itm_id ='$id_categoria' and hr_mar_ini < '$hora' and 
        hr_mar_cie > '$hora'") or die (mysql_error());
    }
    //Miercoles
    elseif ($dia == "Wednesday") 
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE itm_id ='$id_categoria' and hr_mier_ini < '$hora' and 
        hr_mier_cie > '$hora'") or die (mysql_error());
    }
    //Jueves
    elseif ($dia == "Thursday") 
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE itm_id ='$id_categoria' and hr_juev_ini < '$hora' and 
        hr_juev_cie > '$hora'") or die (mysql_error());
    }
    //Viernes
    elseif ($dia == "Friday") 
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE itm_id ='$id_categoria' and hr_vier_ini < '$hora' and 
        hr_vier_cie > '$hora'") or die (mysql_error());
    }
    //Sabado
    elseif ($dia == "Saturday") 
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE itm_id ='$id_categoria' and hr_sab_ini < '$hora' and 
        hr_sab_cie > '$hora'") or die (mysql_error());
    }
    //Domingo
    elseif ($dia == "Sunday") 
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE itm_id ='$id_categoria' and hr_dom_ini < '$hora' and 
        hr_dom_cie > '$hora'") or die (mysql_error());
    }

    while ($r_filtro_disponible = mysql_fetch_array($filtro_disponible))
    {
      $id_disponible = $r_filtro_disponible['itm_id'];
      $filtro_distancia_fecha = mysql_query("SELECT itm_id, itm_latitud, itm_longitud FROM itm_item 
        WHERE itm_id ='$id_disponible' and itm_fecha_inicio < '$fecha' and itm_fecha_termino > '$fecha'") or die (mysql_error());

      while ($r_filtro_distancia_fecha= mysql_fetch_array($filtro_distancia_fecha))
      {
        $itm_latitud= $r_filtro_distancia_fecha['itm_latitud'];
        $itm_longitud = $r_filtro_distancia_fecha['itm_longitud'];

        $distancia = distanciaGeodesica($latitud, $longitud, $itm_latitud, $itm_longitud);

        if ($distancia<=$radioBusqueda)
        {
        	//Arreglo items que pasan filtro
        	$id_itm[$item_id] = $r_filtro_distancia_fecha['itm_id'];
          $item_id++;
        }       
      }
    }
  }

unset($r_filtro_disponible, $r_filtro_distancia_fecha, $consulta_usuarios, $reg4);

//id_grupo del usuario
$inf_usuario = mysql_query("SELECT usr_grupo FROM usr_usuarios WHERE usr_mail = '$usuario' or usr_nick = '$usuario'") or die (mysql_error());
while($reg_inf=mysql_fetch_array($inf_usuario))
{
  $usr_grupo = $reg_inf['usr_grupo'];
}

$tabla = "rec_grupo".$usr_grupo;

for ($i=0; $i < count($id_itm); $i++) 
{ 
  echo $id_itm[$i]."<br>";
  //consulto tabla recomendacion
  $sql_pearson = mysql_query("SELECT pearson FROM $tabla WHERE itm_id = $id_itm[$i]") or die (mysql_error());
  while ($r_sql_pearson=mysql_fetch_array($sql_pearson)) 
  {
    $data = explode("|", $r_sql_pearson['pearson']);
    $item [$i] = $id_itm[$i];
    for ($x=0; $x < count($data); $x++) 
    { 
      if ($valor < $data[$x] && $data[$x] != 1) 
      {
        $mayor[$i] = $data[$x];
      }
    }
    $valor = 0;
  }
}
echo "N° item: ".count($item)." N° mayor: ".count($mayor)."<br>";
for ($i=0; $i < count($item); $i++) 
{ 
  echo "Item: ".$item[$i]." Mayor: ".$mayor[$i]."<br>";
}
//Ordeno los mas importantes
foreach ($mayor as $key => $val) 
{
    $recomendacion[$indice] = $item[$key];
    $indice++;
}

//Busco los 5 mayores
if(5 <= $indice)
{
	for ($i=0; $i < 5 ; $i++) 
	{ 
		$sql_recomendacion = mysql_query("SELECT itm_nombre, itm_direccion, itm_promedio, itm_latitud, itm_longitud FROM itm_item 
        WHERE itm_id ='$recomendacion[$i]'") or die (mysql_error());

		while ($r_recomendacion= mysql_fetch_array($sql_recomendacion))
		{
			$datos [$d] ["resultado"] = "1";                                                           
      $datos [$d] ["itm_nombre"] = $r_recomendacion['itm_nombre'];                         
      $datos [$d] ["itm_direccion"] = utf8_decode($r_recomendacion['itm_direccion']);
      $datos [$d] ["itm_promedio"] = $r_recomendacion["itm_promedio"];
      $datos [$d] ["distancia"] = distanciaGeodesica($latitud, $longitud, $r_recomendacion['itm_latitud'], $r_recomendacion['itm_longitud']);
      $datos [$d] ["itm_latitud"] = $r_recomendacion['itm_latitud'];
      $datos [$d] ["itm_longitud"] = $r_recomendacion['itm_longitud'];
	    $d++;
		}
	}
}
else
{
	for ($i=0; $i < $indice ; $i++) 
	{ 
		$sql_recomendacion = mysql_query("SELECT itm_nombre, itm_direccion, itm_promedio, itm_latitud, itm_longitud FROM itm_item 
        WHERE itm_id ='$recomendacion[$i]'") or die (mysql_error());

		while ($r_recomendacion= mysql_fetch_array($sql_recomendacion))
		{
			$datos [$d] ["resultado"] = "1";                                                           
          	$datos [$d] ["itm_nombre"] = $r_recomendacion['itm_nombre'];                         
          	$datos [$d] ["itm_direccion"] = utf8_decode($r_recomendacion['itm_direccion']);
          	$datos [$d] ["itm_promedio"] = $r_recomendacion["itm_promedio"];
          	$datos [$d] ["distancia"] = distanciaGeodesica($latitud, $longitud, $r_recomendacion['itm_latitud'], $r_recomendacion['itm_longitud']);
          	$datos [$d] ["itm_latitud"] = $r_recomendacion['itm_latitud'];
          	$datos [$d] ["itm_longitud"] = $r_recomendacion['itm_longitud'];
	        $d++;
		}
	}
}

if (mysql_num_rows ($filtro_categoria) == 0) 
{
	$datos [0] ["resultado"] = "categoria";
}
elseif ($item_id==0) 
{
	$datos [0] ["resultado"] = "radioBusqueda";
}
echo $json->encode($datos);
$tiempo_fin = microtime_float();
echo "<br>Tiempo empleado: " . ($tiempo_fin - $tiempo_inicio);
function microtime_float()
{
list($useg, $seg) = explode(" ", microtime());
return ((float)$useg + (float)$seg);
}
?>