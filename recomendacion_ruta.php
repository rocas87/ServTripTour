<?php
header("Content-Type: text/html;charset=utf-8");
error_reporting(1);
set_time_limit (3600);

include("conexion.php");
include("pearson.php");
include("calculoDistancia.php");
include("parseador.php");
require_once("JSON.php");


//Variables resividas desde el usuario
/*
$usuario = $_POST['usuario'];
$latitud = $_POST["latitud"];
$longitud = $_POST["longitud"];
$distMaxima = $_POST["distMaxima"];
$tRecorrido = $_POST["tiempoRecorrido"]; // dias-horas-minutos
$mode = $_POST["mode"];
*/
$latitud = "-33.4547699";
$longitud = "-70.64989889999998";
$mode = "driving";
$distMaxima = 7;
$usuario = "benjamin.g";
$tRecorrido = "0,0,8"; // dias-horas-minutos
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

//id_grupo del usuario
$inf_usuario = mysql_query("SELECT usr_grupo FROM usr_usuarios WHERE usr_mail = '$usuario' or usr_nick = '$usuario'") or die (mysql_error());
while($reg_inf=mysql_fetch_array($inf_usuario))
{
	$usr_grupo = $reg_inf['usr_grupo'];
}

unset($inf_usuario, $reg_inf);

    //Lunes
    if($dia == "Monday")
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE hr_lun_ini < '$hora' and hr_lun_cie > '$hora'") 
      or die (mysql_error());
    }
    //Martes
    elseif($dia == "Tuesday")
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE hr_mar_ini < '$hora' and hr_mar_cie > '$hora'") 
      or die (mysql_error());
    }
    //Miercoles
    elseif ($dia == "Wednesday") 
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE hr_mier_ini < '$hora' and hr_mier_cie > '$hora'") 
      or die (mysql_error());
    }
    //Jueves
    elseif ($dia == "Thursday") 
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE hr_juev_ini < '$hora' and hr_juev_cie > '$hora'") 
      or die (mysql_error());
    }
    //Viernes
    elseif ($dia == "Friday") 
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE hr_vier_ini < '$hora' and hr_vier_cie > '$hora'") 
      or die (mysql_error());
    }
    //Sabado
    elseif ($dia == "Saturday") 
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE hr_sab_ini < '$hora' and hr_sab_cie > '$hora'") 
      or die (mysql_error());
    }
    //Domingo
    elseif ($dia == "Sunday") 
    {
      $filtro_disponible = mysql_query("SELECT itm_id FROM itm_horario WHERE hr_dom_ini < '$hora' and hr_dom_cie > '$hora'") 
      or die (mysql_error());
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

        if ($distancia<=$distMaxima)
        {
        	//Arreglo items que pasan filtro
        	$id_itm[$item_id] = $r_filtro_distancia_fecha['itm_id'];
          	$item_id++;
        }       
      }
    }
//id de todos los usuarios que persenecen al grupo
$consulta_usuarios = mysql_query("SELECT usr_id FROM usr_usuarios WHERE usr_grupo = '$usr_grupo'") or die (mysql_error());
while($reg4 = mysql_fetch_array($consulta_usuarios))	
	{
		//Arreglo usuarios similares
		$id_usuarios[$usuario_id] = $reg4['usr_id'];
		$usuario_id++;
	}

unset($r_filtro_disponible, $r_filtro_distancia_fecha, $consulta_usuarios, $reg4);

//Genero la matriz de Calificaciones de Usuario v/s items
$aux_columna = 1;
$item_usuario[0][0]="i/u";
for($columna=0; $columna < $usuario_id; $columna++)
	{
		$item_usuario[0][$aux_columna] = $id_usuarios[$columna];
		$id_usuario = $id_usuarios[$columna];
		$aux_fila = 1;
		for($fila=0; $fila< $item_id; $fila++)
			{
				
				$item_usuario[$aux_fila][0] = $id_itm[$fila];
				$id_item = $id_itm[$fila];
			
				$q_calificaciones = mysql_query("SELECT itm_rating FROM itm_calificacion WHERE usr_id = '$id_usuario' and 
					itm_id = '$id_item'") or die (mysql_error());
				if (mysql_num_rows ($q_calificaciones) == 0) 
				{
					$item_usuario[$aux_fila][$aux_columna] = 0;
				}
				else
				{
					while($reg5 = mysql_fetch_array($q_calificaciones))	
					{
						$item_usuario[$aux_fila][$aux_columna] = $reg5['itm_rating'];
					}
				}
				
				$aux_fila++;
			}
		
		$aux_columna++;
	}

unset($id_usuarios, $id_itm, $reg5, $aux_columna, $aux_fila);

//Calcula la matriz de Pearson	

for ($filPear=1; $filPear <= $item_id; $filPear++) 
{
	//Arreglo1
	for ($i=1; $i <= $usuario_id; $i++) 
	{ 
		$ar[$i-1] = $item_usuario[$filPear][$i];
	}
	for ($colPear=1; $colPear <= $item_id; $colPear++) 
	{ 
		//$arr2 = arreglo2($columna, $usuario_id, $item_usuario);
		for ($i=1; $i <= $usuario_id; $i++) 
		{ 
			$arr[$i-1] = $item_usuario[$colPear][$i];
		}
		$pearson[$filPear][$colPear] = abs(CorrelacionPearson($ar, $arr));
	}
}

//Selecciono los mayores
for($fila=1; $fila <= $item_id; $fila++)
{
	for($columna=1; $columna <= $item_id; $columna++)
	{
		if ($valor < $pearson[$fila][$columna] && $pearson[$fila][$columna] != 1) 
		{
			$valor = $pearson[$fila][$columna];
			$mayor[$fila] = $valor;
		}
	}
	$valor = 0;
}

//Ordeno los mas importantes
arsort($mayor);
foreach
 ($mayor as $key => $val) 
{
    $recomendacion[$indice] = $item_usuario[$key][0];
    $indice++;
}

//Almaceno 5 mayores
if(5 < $indice)
{
    for ($i=0; $i < 5 ; $i++) 
    {
        $combina[$i] = $recomendacion[$i];
    } 
}
else
{
    for ($i=0; $i < count($recomendacion) ; $i++) 
    {
        $combina[$i] = $recomendacion[$i];
    }
}

//Genera combinaciones posibles
$combinados = combinado($combina);

for ($i=0; $i < count($combinados) ; $i++) 
{ 
  $arr_prom[$i]= promComb($combinados[$i]);
}

//ordeno los mejores promedios
$indice = 0;
unset($combina);
arsort($arr_prom);
foreach
 ($arr_prom as $key => $val) 
{
    $sort_average[$indice] = $arr_prom[$key];
    $combina[$indice] = $combinados[$key];
    $indice++;
}
/*
  $rta[0] = $duracion;
  $rta[1] = $distancia;
  $rta[2] = $coordenadas;                                                           
  $rta[3] = $itm_nombre;
*/
$i=0;
while ($d < 5) 
{
  $desc_ruta = datosRuta($latitud, $longitud, $combina[$i], $mode);
  $dist = (explode(" ", $desc_ruta[1]));
  $tiempo = explode(",", $tRecorrido);

  if ((float) $dist[0] <= $distMaxima && (float) (parsear($desc_ruta[0],""," day")) <= (float)($tiempo[0])
      && (float) (parsear($desc_ruta[0],""," hours")) <= (float)($tiempo[1]) 
      && (float) (parsear($desc_ruta[0],""," mins")) <= (float)($tiempo[2])) 
    {
      $datos [$d] ["rta_nombre"] = $desc_ruta[3];                         
      $datos [$d] ["rta_promedio"] = $sort_average[$i];
      $datos [$d] ["rta_distancia"] = $desc_ruta[1];
      $datos [$d] ["rta_duracion"] = $desc_ruta[0];
      $datos [$d] ["rta_coordenadas"] = $desc_ruta[2];
      $d++;
    }
    $i++;   
    if ($i == count($combina))
    {
      $d = 5;
    }       
  }

echo $json->encode($datos);

//Funcuiones

function combinado($elementos) 
{
  $combinaciones[0] = 0;
  $aux=0;
  for ($i = 0; $i < count($elementos); $i++) {
    for ($j = 1; $j < count($elementos); $j++) 
    {
      if ($elementos[$i] != $elementos[$j] && $i < $j) 
      {
        $combinaciones[$aux] = $elementos[$i].",".$elementos[$j];
        $aux++;
      }

      for ($k = 2; $k < count($elementos); $k++) 
      {
        if ($elementos[$i] != $elementos[$j] && $elementos[$i] != $elementos[$k] && $elementos[$j] != $elementos[$k]
            && $i < $j && $i < $k && $j < $k) 
        {
          $combinaciones[$aux] = $elementos[$i].",".$elementos[$j].",".$elementos[$k];
          $aux++;
        }

        for ($l = 3; $l < count($elementos); $l++) 
        {
          if ($elementos[$i] != $elementos[$j] && $elementos[$i] != $elementos[$k] && $elementos[$i] != $elementos[$l]
              && $elementos[$j] != $elementos[$k] && $elementos[$j] != $elementos[$l] && $elementos[$k] != $elementos[$l]
              && $i < $j && $i < $k && $i < $l
              && $j < $k && $j < $l && $k < $l)
          {
            $combinaciones[$aux] = $elementos[$i].",".$elementos[$j].",".$elementos[$k].",".$elementos[$l];
            $aux++;
          }

          for ($m=4; $m < count($elementos); $m++) 
          {
            if($elementos[$i] != $elementos[$j] && $elementos[$i] != $elementos[$k] && $elementos[$i] != $elementos[$l] && $elementos[$i] != $elementos[$m]
               && $elementos[$j] != $elementos[$k] && $elementos[$j] != $elementos[$l] && $elementos[$j] != $elementos[$m]
               && $elementos[$k] != $elementos[$l] && $elementos[$k] != $elementos[$m]
               && $elementos[$l] != $elementos[$m]
               && $i < $j && $i < $k && $i < $l && $i < $m
               && $j < $k && $j < $l && $j < $m
               && $k < $l && $k < $m
               && $l < $m)
            {
                $combinaciones[$aux] = $elementos[$i].",".$elementos[$j].",".$elementos[$k].",".$elementos[$l].",".$elementos[$m];
                $aux++;
            }
          }
        }
      }
    }
  }
  return $combinaciones;
}

function promComb($combi)
{
  $exp = explode(",", $combi);
  for ($i=0; $i < count($exp); $i++) 
  { 
    $sql_recomendacion = mysql_query("SELECT itm_promedio FROM itm_item WHERE itm_id ='$exp[$i]'") or die (mysql_error());
    while ($r_recomendacion= mysql_fetch_array($sql_recomendacion)) 
    {
      $itm_promedio[$i] = $r_recomendacion["itm_promedio"];
    }
    return average($itm_promedio);
  }
}
function average($arr)
{
    $sum = Sumatorio($arr);
    $num = count($arr);
   
    if($num>0):
        return $sum/$num;
    else:
        return NULL;
    endif;
}
function datosRuta($org_latitud, $org_longitud, $combinado, $mode)
{
  $map_url = "https://maps.googleapis.com/maps/api/directions/xml?&origin=".$org_latitud.",".$org_longitud."&waypoints=optimize:true";
  $div_item = explode(",", $combinado);
  for ($i=0; $i < count($div_item); $i++) 
  { 
     $sql_recomendacion = mysql_query("SELECT itm_nombre, itm_latitud, itm_longitud FROM itm_item WHERE itm_id ='$div_item[$i]'") or die (mysql_error());
     while ( $r_recomendacion= mysql_fetch_array($sql_recomendacion)) 
     {
       $map_url = $map_url."|".$r_recomendacion['itm_latitud'].",". $r_recomendacion['itm_longitud'];
       $coordenadas = $coordenadas."|".$r_recomendacion['itm_latitud'].",". $r_recomendacion['itm_longitud'];
       $itm_nombre = $itm_nombre.",".$r_recomendacion['itm_nombre'];                         
     }
  }
  $map_url = $map_url."&mode=".$mode."&language=en&region=cl&sensor=false";
  $response_xml_data = file_get_contents($map_url);
  $xml = simplexml_load_string($response_xml_data);
 
  $rta[0] = (string) ($xml->route->leg->duration->text);
  $rta[1] = (string) ($xml->route->leg->distance->text);
  $rta[2] = $coordenadas;                                                           
  $rta[3] = $itm_nombre;
  return $rta;
}
?>