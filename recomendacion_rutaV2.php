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

$usuario = $_POST['usuario'];
$latitud = $_POST["latitud"];
$longitud = $_POST["longitud"];
$distMaxima = $_POST["distMaxima"];
$tRecorrido = $_POST["tiempoRecorrido"]; // dias-horas-minutos
$mode = $_POST["mode"];
/*
$latitud = "-33.4547699";
$longitud = "-70.64989889999998";
$mode = "driving";
$distMaxima = 100;
$usuario = "benjamin.g";
$tRecorrido = "0,0,8"; // dias-horas-minutos
*/
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

//id_grupo del usuario
$inf_usuario = mysql_query("SELECT usr_grupo FROM usr_usuarios WHERE usr_mail = '$usuario' or usr_nick = '$usuario'") or die (mysql_error());
while($reg_inf=mysql_fetch_array($inf_usuario))
{
  $usr_grupo = $reg_inf['usr_grupo'];
}

$tabla = "rec_grupo".$usr_grupo;

//consulto tabla recomendacion
for ($i=0; $i < count($id_itm); $i++) 
{ 
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

//Ordeno los mas importantes
arsort($mayor);
foreach ($mayor as $key => $val) 
{
    $recomendacion[$indice] = $item[$key];
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
  $rta[0] = $duracion en segudos;
  $rta[1] = $distancia;
  $rta[2] = $coordenadas;                                                           
  $rta[3] = $itm_nombre;
  $rta[4] = $promedio;
  $rat[5] = $direccion;
  $rta[0] = $duracion en dias horas y segundos;
*/  
$i=0;
while ($d < 5) 
{
  $desc_ruta = datosRuta($latitud, $longitud, $combina[$i], $mode);  
  $dist = (explode(" ", $desc_ruta[1]));
  $tiempo = explode(",", $tRecorrido);
  $duracion = ($tiempo[0]*86400)+($tiempo[1]*3600)+($tiempo[2]*60);

  if ((float) $dist[0] <= $distMaxima && (float) $desc_ruta[0] <= (float) $duracion) 
    {
      $datos [$d] ["resultado"] = 1;
      $datos [$d] ["rta_nombre"] = $desc_ruta[3];                         
      $datos [$d] ["rta_promedio"] = $sort_average[$i];
      $datos [$d] ["rta_distancia"] = $desc_ruta[1];
      $datos [$d] ["rta_duracion"] = $desc_ruta[6];
      $datos [$d] ["rta_coordenadas"] = $desc_ruta[2];
      $datos [$d] ["itm_promedio"] = $desc_ruta[4];
      $datos [$d] ["itm_direccion"] = $desc_ruta[5];
      $d++;
    }
    $i++;   
    if ($i == count($combina))
    {
      $d = 5;
    }       
  }

if ($d==0)
{
  $datos [0] ["resultado"] = "nada";
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
  unset($itm_promedio, $itm_direccion, $itm_nombre);
  $map_url = "https://maps.googleapis.com/maps/api/directions/xml?&origin=".$org_latitud.",".$org_longitud."&waypoints=optimize:true";
  $div_item = explode(",", $combinado);
  for ($i=0; $i < count($div_item); $i++) 
  { 
     $sql_recomendacion = mysql_query("SELECT itm_nombre, itm_direccion, itm_promedio, itm_latitud, itm_longitud FROM itm_item WHERE itm_id ='$div_item[$i]'") or die (mysql_error());
     while ( $r_recomendacion= mysql_fetch_array($sql_recomendacion)) 
     {
       $map_url = $map_url."|".$r_recomendacion['itm_latitud'].",". $r_recomendacion['itm_longitud'];
       $coordenadas = $coordenadas.$r_recomendacion['itm_latitud'].",". $r_recomendacion['itm_longitud'].",";
       $itm_nombre = $itm_nombre.$r_recomendacion['itm_nombre'].","; 
       $itm_promedio = $itm_promedio.$r_recomendacion['itm_promedio'].",";
       $itm_direccion = $itm_direccion.$r_recomendacion['itm_direccion']."|";    
     }
  }
  $map_url = $map_url."&mode=".$mode."&language=en&region=cl&sensor=false";
  $response_xml_data = utf8_encode(file_get_contents($map_url));
  $xml = simplexml_load_string($response_xml_data);
  $rta[0] = (string) ($xml->route->leg->duration->value);
  $rta[1] = (string) ($xml->route->leg->distance->text);
  $rta[2] = $coordenadas;                                                           
  $rta[3] = $itm_nombre;
  $rta[4] = $itm_promedio;
  $rta[5] = $itm_direccion;
  $rta[6] = (string) ($xml->route->leg->duration->text);
  return $rta;
}
?>