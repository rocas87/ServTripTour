<?php
header("Content-Type: text/html;charset=utf-8");

include("conexion.php");
include("calculoDistancia.php");
require_once("JSON.php");  

$latitud = $_POST["latitud"];
$longitud = $_POST["longitud"];
$categoria = $_POST["categoria"];
$radioBusqueda = $_POST["radioBusqueda"];

$json = new Services_JSON;
$i = 0;
$datos=array();
$hoy = getdate();
$fecha = "1900-".$hoy["mon"]."-".$hoy["mday"];
$dia = $hoy ["weekday"];
$hora = $hoy ["hours"].":".$hoy["minutes"].":00.000000";

$con = mysql_connect($host,$userDb,$passDb) or die("Sin conexion");
mysql_select_db($db); 

$filtro_categoria = mysql_query("SELECT itm_id FROM itm_categoria WHERE cat_id = '$categoria' ") or die (mysql_error());
if (mysql_num_rows ($filtro_categoria) == 0) 
{
      $datos [0] ["resultado"] = "categoria";
}
else
{
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
      $filtro_distancia_fecha = mysql_query("SELECT itm_nombre, itm_direccion, itm_promedio, itm_latitud, itm_longitud FROM itm_item 
        WHERE itm_id ='$id_disponible' and itm_fecha_inicio < '$fecha' and itm_fecha_termino > '$fecha'") or die (mysql_error());

      while ($r_filtro_distancia_fecha= mysql_fetch_array($filtro_distancia_fecha))
      {
        $itm_latitud= $r_filtro_distancia_fecha['itm_latitud'];
        $itm_longitud = $r_filtro_distancia_fecha['itm_longitud'];

        $distancia = distanciaGeodesica($latitud, $longitud, $itm_latitud, $itm_longitud);

        if ($distancia<=$radioBusqueda)
        {
          $datos [$i] ["resultado"] = "1";                                                           
          $datos [$i] ["itm_nombre"] = $r_filtro_distancia_fecha['itm_nombre'];                         
          $datos [$i] ["itm_direccion"] = utf8_decode($r_filtro_distancia_fecha['itm_direccion']);
          $datos [$i] ["itm_promedio"] = $r_filtro_distancia_fecha["itm_promedio"];
          $datos [$i] ["distancia"] = $distancia;
          $datos [$i] ["itm_latitud"] = $itm_latitud;
          $datos [$i] ["itm_longitud"] = $itm_longitud;
          $i++;
        }       
      }
    }
  }
}

if ($i==0) 
{
  $datos [0] ["resultado"] = "radioBusqueda";
}
echo $json->encode($datos);
?>