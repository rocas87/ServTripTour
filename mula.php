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

$latitud = "-33.4547699";
$longitud = "-70.64989889999998";
$mode = "driving";
$distMaxima = 1000;
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

//Conexion BdD
$con=mysql_connect($host,$userDb,$passDb) or die ("problemas con servidor");
mysql_select_db($db,$con) or die("problemas con bd");

$hoy = getdate();
$fecha = "1900-".$hoy["mon"]."-".$hoy["mday"];
$dia = $hoy ["weekday"];
$hora = $hoy ["hours"].":".$hoy["minutes"].":00.000000";

for ($i=0; $i < 5; $i++) 
{ 
  $combina[$i] = $i;
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
  $rta[4] = $promedio;
  $rat[5] = $direccion;
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
      $datos [$d] ["resultado"] = 1;
      $datos [$d] ["rta_nombre"] = $desc_ruta[3];                         
      $datos [$d] ["rta_promedio"] = $sort_average[$i];
      $datos [$d] ["rta_distancia"] = $desc_ruta[1];
      $datos [$d] ["rta_duracion"] = $desc_ruta[0];
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
  $rta[0] = (string) ($xml->route->leg->duration->text);
  $rta[1] = (string) ($xml->route->leg->distance->text);
  $rta[2] = $coordenadas;                                                           
  $rta[3] = $itm_nombre;
  $rta[4] = $itm_promedio;
  $rta[5] = $itm_direccion;
  return $rta;
}
?>